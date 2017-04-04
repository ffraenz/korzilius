
import React from 'react'

let uidIterator = 0

export default class FormRadio extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      value: this.props.default || this.props.choices[0]
    }
  }

  componentWillMount () {
    this.name = 'form-radio-' + (++ uidIterator)
  }

  onChange (evt) {
    let value = evt.target.value
    this.setState({ value })

    if (this.props.onChange) {
      this.props.onChange(this, value)
    }
  }

  get value () {
    return this.state.value
  }

  render () {
    let choiceLabels = this.props.choiceLabels
    return (
      <div className="form-radio">
       { this.props.choices.map(
         (choice, index) =>
        <div className="form-radio__choice">
          <input
            className="form-radio__input"
            type="radio"
            name={this.name}
            id={`${this.name}-${index}`}
            value={choice}
            checked={this.state.value === choice}
            onChange={this.onChange.bind(this)} />
          <label
            className="form-radio__label"
            htmlFor={`${this.name}-${index}`}
          >{choiceLabels ? choiceLabels[index] : choice}</label>
        </div>
       ) }
      </div>
    )
  }
}
