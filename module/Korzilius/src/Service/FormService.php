<?php

namespace Korzilius\Service;

use FPDI;
use DateTime;

use Korzilius\Exception\FormServiceException;

class FormService {

  protected $forms = [
    'protocole-entrevue' => [
      'header' => true,
      'template' => 'protocole-entrevue.pdf',
      'handler' => 'drawProtocoleEntrevueForm',
    ],
    'note-departement' => [
      'header' => false,
      'template' => 'note-departement.pdf',
      'handler' => 'drawNoteDepartementForm',
    ],
    'estimation' => [
      'header' => true,
      'template' => 'estimation.pdf',
    ],
    'inventaire-avant-projet' => [
      'header' => true,
      'template' => 'inventaire-avant-projet.pdf',
    ],
    'ventilation-vehicules-automoteurs-agricoles' => [
      'header' => true,
      'template' => 'ventilation-vehicules-automoteurs-agricoles.pdf',
    ],
  ];

  public function createForm($name, $data) {

    if (!isset($this->forms[$name])) {
      throw new FormServiceException(sprintf(
        'Form named \'%s\' does not exist.',
        $name), 404);
    }

    $config = $this->forms[$name];

    // create pdf document
    $pdf = new FPDI('P', 'mm', 'A4', true, 'UTF-8', false, true);

    // remove default header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // set font
    $pdf->setFont('Helvetica', 'B', 10);

    // set template source
    $pageCount = $pdf->setSourceFile('data/templates/' . $config['template']);

    // add pages
    for ($i = 0; $i < $pageCount; $i ++) {
      $pdf->addPage('P', 'A4');

      // set template of this page
      $pdf->useTemplate($pdf->importPage($i + 1), 0, 0, 210, 297, true);

      // draw header
      if ($config['header']) {

        $client = isset($data['client']) ? $data['client'] : null;
        $subject = isset($data['subject']) ? $data['subject'] : null;
        $date = isset($data['date']) ? $data['date'] : null;

        ($i === 0)
          ? $this->drawFrontHeader($pdf, $client, $subject, $date)
          : $this->drawFollowingHeader($pdf, $client, $subject, $date);
      }
    }

    // select first page
    $pdf->setPage(1);

    // run drawing handler
    if (isset($config['handler']) && $config['handler']) {
      $handler = $this->{$config['handler']}($pdf, $data);
    }

    // export
    $data = $pdf->getPDFData();
    unset($pdf);

    // write to tmp file
    $filename = tempnam(sys_get_temp_dir(), 'korzilius-form-export');
    $success = file_put_contents($filename, $data);
    unset($data);

    if (!$success) {
      throw new FormServiceException(sprintf(
        'Unable to save form export to %s.',
        $filename), 500);
    }

    // TODO: Remove this debug block
    if (false) {
      header('Content-Type: application/pdf');
      readfile($filename);
      die();
    }

    return $filename;
  }

  public function drawFrontHeader(
    $pdf, $client = null, $subject = null, $date = null) {

    // row and col positions
    $cols = [12, 90.5, 149.5];
    $rows = [36.25, 47.25, 58, 68.75, 79.5, 98.5];

    if ($client) {

      // name
      $pdf->text($cols[0], $rows[0], $client->getFullNameLabel());

      // street
      !empty($client->getStreetLabel()) &&
        $pdf->text($cols[0], $rows[1], $client->getStreetLabel());

      // location
      !empty($client->getLocationLabel()) &&
        $pdf->text($cols[0], $rows[2], $client->getLocationLabel());

      // birthdate
      !empty($client->getBirthdate()) &&
        $pdf->text($cols[0], $rows[3],
          $client->getBirthdate()->format('d.m.Y'));

      // phone
      !empty($client->getPhonePrivate()) &&
        $pdf->text($cols[1], $rows[1], $client->getPhonePrivate());

      // phone pro
      !empty($client->getPhonePro()) &&
        $pdf->text($cols[2], $rows[1], $client->getPhonePro());

      // mobile
      !empty($client->getMobilePrivate()) &&
        $pdf->text($cols[1], $rows[2], $client->getMobilePrivate());

      // mobile pro
      !empty($client->getMobilePro()) &&
        $pdf->text($cols[2], $rows[2], $client->getMobilePro());

      // email
      !empty($client->getEmailPrivate()) &&
        $pdf->text($cols[1], $rows[3], $client->getEmailPrivate());

      // email pro
      !empty($client->getEmailPro()) &&
        $pdf->text($cols[2], $rows[3], $client->getEmailPro());

      // record state
      $recordState = implode(' / ', array_filter([
        $client->getLaluxClientId(),
        $client->getUpdateTime()
          ? $client->getUpdateTime()->format('d.m.Y')
          : null,
      ]));

      !empty($recordState) &&
        $pdf->text($cols[0], $rows[4], $recordState);

      // fax
      !empty($client->getFax()) &&
        $pdf->text($cols[1], $rows[4], $client->getFax());
    }

    // subject
    !empty($subject) &&
      $pdf->text($cols[0], $rows[5], $subject);

    // date
    $date && $pdf->text($cols[2], $rows[5], $date->format('d.m.Y'));
  }

  public function drawFollowingHeader(
    $pdf, $client = null, $subject = null, $date = null) {

    $cols = [12.5, 92.25, 152.5];
    $rows = [17];

    // subject
    !empty($subject) &&
      $pdf->text($cols[0], $rows[0], $subject);

    // date
    ($date !== null) &&
      $pdf->text($cols[2], $rows[0], $date->format('d.m.Y'));
  }

  public function drawProtocoleEntrevueForm($pdf, $data) {

    // text
    if (isset($data['text'])) {
      $text = $data['text'];
      $pdf->setXY(16.5, 119);
      $pdf->setCellHeightRatio(1.395);
      $pdf->multicell(177, 160, $text, 0, 'L');
    }

    // select second page
    $pdf->setPage(2);

    // contracts
    $contracts = '00/123456, 00/123456, 00/123456';
    !empty($contracts) &&
      $pdf->text(12.5, 28, $contracts);
  }

  public function drawNoteDepartementForm($pdf, $data) {

    if (isset($data['client']) && $data['client']) {
      $client = $data['client'];

      // name
      !empty($client->getNameLabel()) &&
        $pdf->text(27, 47.5, $client->getNameLabel());

      // location
      !empty($client->getLocationLabel()) &&
        $pdf->text(122, 47.5, $client->getLocationLabel());

      // lalux client id
      !empty($client->getLaluxClientId()) &&
        $pdf->text(102, 56, $client->getLaluxClientId());
    }

    // contract id
    $pdf->text(31, 56, '00/123456');

    // agency nr
    $pdf->text(34, 244.5, '12345');

    // agency name
    $pdf->text(131, 244.5, 'Agency');

    // issuer name
    $pdf->text(44.5, 255, 'Max Mustermann');

    // timestamp
    $time = new DateTime();
    $pdf->text(107, 270.5, $time->format('d.m.Y H:i'));

    // issuer signature
    $pdf->text(154, 270.5, 'MUSTERMANN');

    // text
    if (isset($data['text']) && !empty($data['text'])) {
      $pdf->setFont('Helvetica', '', 10);
      $pdf->setXY(15.75, 72);
      $pdf->setCellHeightRatio(1.5);
      $pdf->multicell(178, 165, $data['text'], 0, 'L');
    }
  }
}
