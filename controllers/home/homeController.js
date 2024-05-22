import { createRequire } from "module";

const require = createRequire(import.meta.url);
const axios = require('axios');

export default class homeController {

    async getRooms(user) {
        const rooms = await axios.get('http://192.168.2.56:25565/user/rooms-'+user);

        return rooms.data;
    }

}