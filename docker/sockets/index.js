
import socketIO from 'socket.io'
import express from 'express'
import bodyParser from 'body-parser'

// collect env variables
let apiPort = process.env.API_PORT || 50071
let socketsPort = process.env.SOCKETS_PORT || 50080

// configure sockets server
let server = socketIO(socketsPort);

server.on('connection', socket => {
  console.log('Client connected')

  socket.on('disconnect', () => {
    console.log('Client disconnected')
  })
})

// configure api server
let api = express()
api.use(bodyParser.urlencoded({ extended: true }))
api.use(bodyParser.json())

// configure events route
api.route('/events').post((req, res) => {

  // immediately respond with code 200
  res.status(200).json({ success: true })

  // accept a single or multiple events
  let events = Array.isArray(req.body) ? req.body : [req.body]

  // push each event to every client
  events.forEach(event => {
    server.emit(event.name, event.data)
    console.log(`[${event.name}] ${JSON.stringify(event.data)}`)
  })
})

// start listening for events via rest api
api.listen(apiPort, () => {
  console.log('Sockets server listening on port ' + socketsPort)
  console.log('Api server listening on port ' + apiPort)
})
