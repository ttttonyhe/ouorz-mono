const fs = require("fs");
const path = require("path");
const request = require("request");
const schedule = require('node-schedule');

const scheduleCron = () => {
  schedule.scheduleJob('0 56 2 * * *', () => {
    let dirPath = path.join(__dirname, "../public");
    let fileName = "beacon.min.js";
    let url = "https://static.cloudflareinsights.com/beacon.min.js";
    let stream = fs.createWriteStream(path.join(dirPath, fileName));
    request(url).pipe(stream).on("close", function () {
      console.log("File [" + fileName + "] Downloaded | " + new Date());
    });
  });
}

scheduleCron();