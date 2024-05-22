import { createRequire } from "module";

const require = createRequire(import.meta.url);
const express = require("express");
const { spawn } = require('child_process');
const router = express.Router();
const config = require('config');
const path = require("path");
const bodyParser = require('body-parser'); // Add the body-parser middleware
const fs = require("fs");
var http = require('http');
const ejs = require('ejs'); // Replace with your template engine
var geoip = require('geoip-lite');
const axios = require('axios');
const qs = require('qs');
const querystring = require('querystring');
const SpotifyWebApi = require('spotify-web-api-node');

router.use(bodyParser.urlencoded({ extended: true })); // Parse URL-encoded bodies
router.use(bodyParser.json()); // Parse JSON bodies

router.get('/', async (req, res) => {
    res.render('setup/index');
});

export default router;
