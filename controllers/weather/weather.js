import { createRequire } from "module";

const require = createRequire(import.meta.url);
const express = require("express");
const router = express.Router();
const hue = require('node-hue-api');
const geoip = require('geoip-lite');
const axios = require('axios');
const fs = require("fs");

export default class Weather {

    constructor(ip) {
        this.ip = ip;
    }

    async getWeather() {
        
        const geo = await this.getLongLat(this.ip);
        const latitude = geo["ll"][0];
        const longitude = geo["ll"][1];

        const response = await axios.get(`https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current&forecast_days=7&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,precipitation_probability_mean,uv_index_max`);

        this.saveWeatherToJSON(response.data);

        return response.data;
    }

    async getTodayWeather() {
        const geo = await this.getLongLat(this.ip);
        const latitude = geo["ll"][0];
        const longitude = geo["ll"][1];

        const response = await axios.get(`https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,wind_speed_10m,rain&hourly=temperature_2m,relative_humidity_2m,wind_speed_10m,rain,cloud_cover`);
        const today = response.data;
        console.log(today);
        return today;
    }

    async saveWeatherToJSON(weather) {
        const data = JSON.stringify(weather);
        fs.writeFileSync('weather.json', data);
    }   

    async getWeatherFromJSON() {
        const data = fs.readFileSync('weather.json');
        const weather = JSON.parse(data);
        return weather;
    }

    async getLongLat() {
        var geo = geoip.lookup(this.ip);
        return geo;
    }

}

