import { createRequire } from "module";

const require = createRequire(import.meta.url);
const express = require("express");
const router = express.Router();
const hue = require('node-hue-api');

export class hueClient {



     constructor() {
           this.bridge = null;
           this.brige_address = "192.168.2.37";
      }

   async discoverBridge() {
     try {
          // Discover bridges on the local network
          const discoveryResults = await hue.discovery.nupnpSearch();
     
          if (discoveryResults.length === 0) {
               console.log('No bridges found on the local network.');
               return;
          }
     
          console.log('Found bridges:');
          console.log(discoveryResults);
     
          // Assuming you want to connect to the first bridge found
          const bridge = discoveryResults[0];
          console.log('Bridge IP address:', bridge.ipaddress);

            this.bridge = bridge;
          return bridge;
     } catch (error) {
         console.error('Error:', error);
     }
 }
 

    async getBridgeConfig() {
         const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
         const bridgeConfig = await unauthenticatedApi.configuration.get();
         console.log('Bridge Config:', bridgeConfig);
         return bridgeConfig;
    }

     async getLights() {
          const unauthenticatedApi = await hue.api.createLocal(this.brige_address).connect("FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7");
          const lights = await unauthenticatedApi.lights.getAll();
          return lights;
     }

     async getLightRooms() {
           const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
           const lightRooms = await unauthenticatedApi.groups.getAll();
           console.log('Light Rooms:', lightRooms);
           return lightRooms;
     }


     async  getAllLightsWithRoomNames() {
     try {
        
          const authenticatedApi = await hue.v3.api.createLocal(this.brige_address).connect('FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7');

          // Retrieve all lights from the bridge
          const lights = await authenticatedApi.lights.getAll();

          // Retrieve all groups (rooms) from the bridge
          const groups = await authenticatedApi.groups.getAll();

          // Create a map to store light ID to room name mapping
          const lightIdToRoomName = {};

          // Iterate over each group (room) to extract light ID to room name mapping
          groups.forEach(group => {
               group.lights.forEach(lightId => {
                    lightIdToRoomName[lightId] = group.name;
               });
          });

          // Combine light information with room names
          const lightsWithRoomNames = lights.map(light => {
               return {
                    ...light,
                    roomName: lightIdToRoomName[light.id] || 'Unknown Room'
               };
          });

          return lightsWithRoomNames;
     } catch (error) {
          console.error('Error:', error);
     }
     }


     async getGroups() {
          const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
          const groups = await unauthenticatedApi.groups.getAll();
          console.log('Groups:', groups);
          return groups;
     }

    async getScenes() {
         const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
         const scenes = await unauthenticatedApi.scenes.getAll();
         console.log('Scenes:', scenes);
         return scenes;
    }

    async getRules() {
         const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
         const rules = await unauthenticatedApi.rules.getAll();
         console.log('Rules:', rules);
         return rules;
    }

    async getSchedule() {
         const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
         const schedules = await unauthenticatedApi.schedules.getAll();
         console.log('Schedules:', schedules);
         return schedules;
    }

    async getUserName() {
           const unauthenticatedApi = await hue.api.createLocal(this.bridge.ipaddress).connect();
           const username = await unauthenticatedApi.users.get();
           console.log('Username:', username);
           return username;
     }

     async createUser() {
          const unauthenticatedApi = await hue.api.createLocal(this.brige_address).connect();
          const createdUser = await unauthenticatedApi.users.createUser("My Application Name");
          console.log('Created user:', createdUser);
          return createdUser;
     }

     async turnOnLight(id) {
          const authenticatedApi = await hue.api.createLocal(this.brige_address).connect("FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7");
          const result = await authenticatedApi.lights.setLightState(id, { on: true });
          console.log('Light turned on');
     }

     async turnOffLight(id) {
          const authenticatedApi = await hue.api.createLocal(this.brige_address).connect("FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7");
          const result = await authenticatedApi.lights.setLightState(id, { on: false });
          console.log('Light turned off');
     }

     async setLightColor(id, color) {
          const authenticatedApi = await hue.api.createLocal(this.brige_address).connect("FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7");
          const result = await authenticatedApi.lights.setLightState(id, { xy: color });
          console.log('Color set to', color);
     }

     async setLightBrightness(id, brightness) {
          const authenticatedApi = await hue.api.createLocal(this.brige_address).connect("FnHBuCex5VmOsJINstrtaKabipq-Gfoo-h8ZZcd7");
          const result = await authenticatedApi.lights.setLightState(id, { bri: brightness });
          console.log('Brightness set to', brightness);
     }

     

}