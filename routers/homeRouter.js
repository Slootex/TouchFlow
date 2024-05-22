import { createRequire } from "module";
import Weather from "../controllers/weather/weather.js";
import HotspotController from "../controllers/hotspot/HotspotController.js";
import homeController from "../controllers/home/homeController.js";

const require = createRequire(import.meta.url);
const express = require("express");
const { spawn } = require('child_process');
const router = express.Router();
const config = require('config');
const path = require("path");
const bodyParser = require('body-parser'); // Add the body-parser middleware
const fs = require("fs");
var http = require('http');
let tasks = require('../tasks.json');
const ejs = require('ejs'); // Replace with your template engine
var geoip = require('geoip-lite');
const axios = require('axios');
const qs = require('qs');
const querystring = require('querystring');
const SpotifyWebApi = require('spotify-web-api-node');

router.use(bodyParser.urlencoded({ extended: true })); // Parse URL-encoded bodies
router.use(bodyParser.json()); // Parse JSON bodies


//=========================================================================================================
// [                         ]
// |         ROUTES          |                                                                            |
// [                         ]
//=========================================================================================================


// GET /home
// Render the home page
router.get('/', async (req, res) => {
    let date = new Date();
    let dateTime = "";
    let hours = 24;

    if(date.getHours() < 12) {
        dateTime = "Guten Morgen";
    }
    if(date.getHours() >= 12 && date.getHours() < 18) {
        dateTime = "Hey";
    }
        
    if(date.getHours() >= 18) {
        dateTime = "Guten Abend";
    }

    let hour = date.toLocaleTimeString().split(":")[0];
    let min = date.toLocaleTimeString().split(":")[1];
    if(parseInt(hour) <= 9) {
        hour = "0"+hour;
    }

    let seconds = date.toLocaleTimeString().split(":")[2];

    const tasks = await getTasks();
    var ip = await executePhyton('getIP.py', []); 

    const weatherController = new Weather(ip[0].replace("\r\n", ""));
    const weather = await weatherController.getWeather();
    const currentWeather = await weatherController.getTodayWeather();

    const home = new homeController();
    const rooms = await home.getRooms('1');

    
    res.render('home/index', {dateTime: dateTime, currentWeather: currentWeather, hours: hours, hr: hour, min: min, seconds: seconds, tasks: tasks, weather: weather});
});

router.get("/get-calender", async (req, res) => {
    
    const tasks = await getTasks();

    res.render('components/sideCalender', {tasks: tasks});
});

router.get('/get-weather', async (req, res) => {
    try {

        const ip = await executePhyton('getIP.py', []);

        const weaterClass = new Weather(ip[0].replace("\r\n", ""));
        const weather = await weaterClass.getWeather();
      
        res.render('components/weather/modal', {weather: weather});

    } catch (error) {
        console.error(error);
        res.status(500).send('Error triggering pusher event');
    }
});


//=========================================================================================================
// [                         ]
// |        FUNCTIONS        |                                                                            |
// [                         ]
//=========================================================================================================

const getTasks = async () => {
    const data = await fs.promises.readFile('./tasks.json', 'utf8'); 
    const tasksJson = JSON.parse(data);
    return tasksJson;
}

const executePhyton = async (script, args) => {
    const argStrings  = args.map(arg => arg.toString());

    const pythonProcess = spawn('python', [script, ...argStrings ]);

    let result = [];

    pythonProcess.stdout.on('data', (data) => {
        result.push(data.toString());
    });

    pythonProcess.stderr.on('data', (data) => {
        console.error(`stderr: ${data}`);
    });

    pythonProcess.on('close', (code) => {
        console.log(`child process exited with code ${code}`);
    });

    return new Promise((resolve) => {
        pythonProcess.on('close', () => {
            resolve(result);
        });
    });
}


export default router;