
import React from 'react'

export default class SearchField extends React.Component {

  constructor (props) {
    super(props)
  }

  onKeyUp (evt) {

  }

  render () {
    return (
      <div className="search-field">
        <input
          className="search-field__input"
          placeholder="Sichen"
          keyup={this.onKeyUp.bind(this)} />
      </div>
    )
  }
}
