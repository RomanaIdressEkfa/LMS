/**
 * Custom Next.js server for Hostinger's "Setup Node.js App" (Passenger).
 *
 * Set this file as the "Application startup file" in hPanel. Passenger passes
 * the port/socket to listen on via process.env.PORT. Run `npm run build`
 * before (re)starting the app.
 */
const { createServer } = require("http");
const { parse } = require("url");
const next = require("next");

const port = process.env.PORT || 3000;
const app = next({ dev: false });
const handle = app.getRequestHandler();

app.prepare().then(() => {
  createServer((req, res) => {
    handle(req, res, parse(req.url, true));
  }).listen(port, () => {
    // eslint-disable-next-line no-console
    console.log(`Next.js production server listening on ${port}`);
  });
});
