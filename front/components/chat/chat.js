
import React from 'react'
import MessageThread from '../message-thread/message-thread'

export default class Chat extends React.Component {

  constructor (props) {
    super(props)
    this.$scrollable = null
  }

  render () {
    let messageThreads = this.composeMessageThreads(this.props.messages)

    return (
      <div className="chat">
        <div className="chat__scrollable" ref={$el => this.$scrollable = $el}>
          <div className="chat__content">
            {messageThreads}
          </div>
        </div>
        <div className="chat__compose">
        </div>
      </div>
    )
  }

  componentDidUpdate () {
    // scroll to bottom
    this.$scrollable.scrollTop = this.$scrollable.scrollHeight
  }

  composeMessageThreads (messages) {
    let threads = []
    let threadMessages = []

    messages.forEach((message, index) => {
      // determine if a new thread needs to be opened for this message
      let lastMessage = (index > 0 ? messages[index - 1] : null)

      let newThread = (lastMessage !== null && (
        // create a new thread when 20 min elapsed between messages
        lastMessage.sendTime - message.sendTime > 60 * 20 ||
        // create a new thread when the sender or receiver changed
        lastMessage.receiverClientId !== message.receiverClientId ||
        lastMessage.receiverUserId !== message.receiverUserId ||
        lastMessage.senderClientId !== message.senderClientId ||
        lastMessage.senderUserId !== message.senderUserId
      ))

      if (newThread) {
        threads.unshift(<MessageThread messages={threadMessages} />)
        threadMessages = []
      }

      // compose message
      threadMessages.unshift(message)
    })

    if (threadMessages.length > 0) {
      // append last thread
      threads.unshift(<MessageThread messages={threadMessages} />)
    }

    return threads
  }
}
