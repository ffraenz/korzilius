
import React from 'react'

import MessageThread from '../message-thread/message-thread'

const Chat = props => {
  let messageThreads = composeMessageThreads(props.messages)

  return (
    <div className="chat">
      {messageThreads}
    </div>
  )
}

function composeMessageThreads (messages) {
  let threads = []
  let threadMessages = []

  messages.forEach((message, index) => {
    // determine if a new thread needs to be opened for this message
    let lastMessage = (index > 0 ? messages[index - 1] : null)

    let newThread = (lastMessage !== null && (
      // create a new thread when 5min elapsed between messages
      lastMessage.sendTime - message.sendTime > 60 * 5 ||
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

export default Chat
