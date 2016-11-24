<?php

namespace Korzilius\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Component extends AbstractHelper {

  protected $_stack;

  protected $_template = null;
  protected $_variables = null;
  protected $_content = null;

  public function __construct() {
    $this->_stack = [];
  }

  public function __invoke($name = null, $variables = []) {
    if ($name !== null) {
      // prepare variables
      $variables = array_merge([
        'classes' => [],
        'modifiers' => [],
      ], $variables);

      $classes = $variables['classes'];

      // add component class
      array_push($classes, $name);

      // add modifier classes
      foreach ($variables['modifiers'] as $modifier) {
        array_push($classes, $name . '--' . $modifier);
      }

      $variables['classes'] = implode(' ', $classes);

      // push component to stack
      array_push($this->_stack, [
        'name' => $name,
        'variables' => $variables
      ]);

      $this->start();
    }
    return $this;
  }

  protected function start() {
    ob_start();
    return $this;
  }

  protected function end() {
    $content = ob_get_clean();
    $entry = array_pop($this->_stack);
    return $this->render($entry['name'], $entry['variables'], $content);
  }

  public function __call($method, $argv) {
    // forward method call to renderer
    // this makes view helpers available inside components
    return call_user_func_array([$this->getView(), $method], $argv);
  }

  public function __get($name) {
    // check if this is a component variable
    if (isset($this->_variables[$name])) {
      return $this->_variables[$name];
    }

    // return variable from renderer
    return $viewVars = $this->getView()->get($name);
  }

  public function __isset($name) {
    // check if variable exists in this component or in view
    return isset($this->_variables[$name]) || isset($this->getView()->{$name});
  }

  public function render($name, $variables = [], $content = '') {
    $this->_template = 'front/components/' . $name . '/' . $name . '.phtml';

    // set content variable
    if (!empty($content)) {
      $variables['content'] = $content;
    }

    $this->_variables = $variables;

    // remove this variable
    if (array_key_exists('this', $variables)) {
      unset($variables['this']);
    }

    // extract all assigned vars
    extract($variables);

    unset($name);
    unset($variables);

    // run template
    try {
      ob_start();
      include $this->_template;
      $this->_content = ob_get_clean();
    } catch (\Exception $ex) {
      ob_end_clean();
      throw $ex;
    }

    return $this->_content;
  }

  public function __toString() {
    return $this->toString();
  }

  public function toString($indent = null) {
    $content = $this->end();
    if ($indent !== null) {
      $whitespace = str_repeat(' ', $indent);
      $content = str_replace("\n", "\n" . $whitespace, $content);
    }
    return $content;
  }
}
