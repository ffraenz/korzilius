
import React from 'react'

export default class FormSelect extends React.Component {

  constructor (props) {
    super(props)

    // initial state
    this.state = {
      value: this.props.default || this.props.choices[0]
    }
  }

  get value () {
    return this.state.value
  }

  onChange (evt) {
    let value = evt.target.value
    this.setState({ value })

    if (this.props.onChange) {
      this.props.onChange(this, value)
    }
  }

  render () {
    let choiceLabels = this.props.choiceLabels
    return (
      <div className="form-select">
        <select
          className="form-select__select"
          onChange={this.onChange.bind(this)}>
          { this.props.choices.map(
            (choice, index) =>
            <option
              value={choice}
              checked={choice === this.state.value}
            >{choiceLabels && choiceLabels[index] || choice}</option>
          ) }
        </select>
      </div>
    )
  }
}
