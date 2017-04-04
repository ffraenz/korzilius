
import React from 'react'

const textareaMinHeight = 82;

export default class FormText extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      value: this.props.default || '',
    }

    this.height = 0;
  }

  componentDidMount () {
    if (this.props.multiline) {
      this.autoSizeMultiline()
    }
  }

  onInput (evt) {
    let value = evt.target.value
    this.setState({ value })

    if (this.props.multiline) {
      this.autoSizeMultiline()
    }

    if (this.props.onChange) {
      this.props.onChange(this, value)
    }
  }

  autoSizeMultiline () {
    // grow textarea with content
    let height = Math.max(this.$textarea.scrollHeight, textareaMinHeight)
    if (height > this.height) {
      this.height = height
      this.$textarea.style.height = height + 'px'
    }
  }

  get value () {
    return this.state.value
  }

  render () {
    return (
      this.props.multiline
      ? <textarea
          className="form-text form-text--multiline"
          ref={$el => this.$textarea = $el}
          value={this.state.value}
          onInput={this.onInput.bind(this)} />
      : <input
          className="form-text"
          type="text"
          value={this.state.value}
          onInput={this.onInput.bind(this)} />
    )
  }
}
