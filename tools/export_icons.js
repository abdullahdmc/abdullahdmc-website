// Copies the cover drawings (tools/cover_icons.js) into the theme as JSON, so the series cards on
// the home page can show the same drawing as each post's cover. Run after changing cover_icons.js:
//   node tools/export_icons.js
const fs = require("fs"), path = require("path"), vm = require("vm");
const src = fs.readFileSync(path.join(__dirname, "cover_icons.js"), "utf8");
const ctx = {};
vm.runInNewContext(src + "\nthis.ICONS = ICONS;", ctx);
const out = path.join(__dirname, "..", "wp-theme", "abdullahdmc", "assets", "cover-icons.json");
fs.writeFileSync(out, JSON.stringify(ctx.ICONS));
console.log(Object.keys(ctx.ICONS).length, "drawings ->", path.relative(process.cwd(), out), fs.statSync(out).size, "bytes");
