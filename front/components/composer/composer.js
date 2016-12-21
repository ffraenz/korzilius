
import React from 'react'

export default class Composer extends React.Component {

  static get defaultProps() {
    return {
      channels: ['intern'],
      channelLabels: ['Intern']
    }
  }

  constructor (props) {
    super(props)

    // select first channel
    this.state = {
      channel: props.channels[0],
      message: ''
    }
  }

  onKeyDown (evt) {
    let code = evt.keyCode || evt.which

    if (code === 13) {
      this.post()
      evt.preventDefault()
    }
  }

  post () {
    if (this.state.message !== '') {
      this.setState({ message: '' })
      this.props.onPost(this.state.channel, this.state.message)
    }
  }

  render () {
    return (
      <div className="composer">
        <ul className="composer__channels">
          {this.props.channels.map((channel, index) => {
            let className = 'composer__channel'
            if (this.state.channel === channel) {
              className += ' composer__channel--active'
            }
            return (
              <li className={className}>
                <a onClick={evt => this.setState({ channel })}>
                  {this.props.channelLabels[index]}
                </a>
              </li>
            )
          })}
        </ul>
        <textarea
          value={this.state.message}
          placeholder="Noriicht antippen + Enter"
          className="composer__textarea"
          onChange={evt => this.setState({ message: evt.target.value })}
          onKeyDown={this.onKeyDown.bind(this)} />
      </div>
    )
  }
}
