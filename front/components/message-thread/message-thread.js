
import React from 'react'
import Message from '../message/message'

const MessageThread = props => {
  let messages = props.messages
  let isOtherSide = (messages[0].senderClientId !== null)

  // compose class names
  let className = ['message-thread'].concat(
    props.classes,
    props.modifiers.map(modifier => 'message-thread--' + modifier),
    isOtherSide ? 'message-thread--side-other' : []
  ).join(' ')

  return (
    <div className={className}>
      <div className="message-thread__source">
        <div className="message-thread__avatar"></div>
      </div>
      <div className="message-thread__messages">
        {messages.map(message => <Message message={message} />)}
      </div>
    </div>
  )
}

MessageThread.defaultProps = {
  classes: [],
  modifiers: [],
  side: 'self'
}

export default MessageThread
