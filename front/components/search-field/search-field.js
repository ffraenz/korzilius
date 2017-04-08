
import React from 'react'

export default class SearchField extends React.Component {

  constructor (props) {
    super(props)

    this.state = {
      value: '',
      searching: false
    }

    this.searching = false
    this.cooldownTimeout = null
  }

  onChange (evt) {
    let $input = evt.target
    let value = $input.value

    this.setState({ value })

    // consider the user to be searching when the field is not empty
    let searching = (value !== '')

    if (this.state.searching !== searching) {
      this.setState({ searching })

      if (searching) {
        // event triggered when the user starts using search
        this.props.onStart()
      } else {
        // event triggered when the user cleared or closed search
        this.props.onFinish()
      }
    }

    if (this.cooldownTimeout !== null) {
      clearTimeout(this.cooldownTimeout)
    }

    this.cooldownTimeout = setTimeout(() => {
      this.cooldownTimeout = null;

      this.props.onSearch(this.state.value)

    }, 300)
  }

  onKeyDown (evt) {
    let code = evt.keyCode || evt.which

    if (code === 38 && this.props.onSelectPreviousResult) {
      this.props.onSelectPreviousResult()
      evt.preventDefault()
    }

    if (code === 40 && this.props.onSelectNextResult) {
      this.props.onSelectNextResult()
      evt.preventDefault()
    }
  }

  render () {
    return (
      <div className="search-field">
        <input
          value={this.state.value}
          className="search-field__input"
          placeholder="Sichen"
          onChange={this.onChange.bind(this)}
          onKeyDown={this.onKeyDown.bind(this)} />
      </div>
    )
  }
}
