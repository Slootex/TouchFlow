import { createRequire } from "module";

const require = createRequire(import.meta.url);
const express = require("express");
const router = express.Router();
const axios = require('axios');
const hotspot = require('node-hotspot');
const { exec } = require('child_process');

const ssid            = 'TouchFlow';
const password        = '1234';

export default class HotspotController {


    async createHotspot() {
      const command = `netsh wlan set hostednetwork mode=allow ssid=${ssid} key=${password}`;
      exec(command, (error, stdout, stderr) => {
        if (error) {
          console.error('Error starting hotspot:', error);
          return;
        }
        console.log('Hotspot started successfully!');
      });
    }



}