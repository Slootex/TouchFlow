import express from 'express';
import http from 'http';
import { Server } from 'socket.io';
import fs from 'fs';
import cors from 'cors';
import homeRouter from './routers/homeRouter.js';
import setupRouter from './routers/setupRouter.js';


const app = express();
const options = {
  key: fs.readFileSync('localhost-key.pem'),
  cert: fs.readFileSync('localhost.pem')
};

const server = http.createServer(options, app);
const io = new Server(server, { cors: { origin: "*" } });

app.set('view engine', 'ejs');
app.use(express.static('public'))
app.use(cors());

// Pass io instance to the router
app.use((req, res, next) => {
  req.io = io;
  next();
});

app.use('/home', homeRouter);
app.use('/setup', setupRouter);

app.use(express.static('public'))

// Start the server
server.listen(3000, () => {
  console.log('Server is running on port 3000');
});



