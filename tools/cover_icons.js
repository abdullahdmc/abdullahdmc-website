// Line drawings for post covers (tools/cover.html). Each icon is SVG markup in a 100x100 box,
// drawn with strokes only; the cover sets the colour and stroke width.
// tools/make_covers.py picks one per post (tools/cover_motifs.json, then keywords, then the topic).

const ring = (n, r1, r2, extra = "") => Array.from({ length: n }, (_, i) => {
  const a = (i / n) * 2 * Math.PI, c = Math.cos(a), s = Math.sin(a);
  return `<line x1="${50 + r1 * c}" y1="${50 + r1 * s}" x2="${50 + r2 * c}" y2="${50 + r2 * s}"/>` +
    (extra ? extra(50 + (r2 + 3) * c, 50 + (r2 + 3) * s) : "");
}).join("");

function helix() {
  const a = [], b = [], rungs = [];
  for (let y = 10; y <= 90; y += 2) {
    const x = 18 * Math.sin((y - 10) / 80 * 2.5 * Math.PI);
    a.push(`${50 + x},${y}`); b.push(`${50 - x},${y}`);
    if (y % 8 === 2 && Math.abs(x) > 5) rungs.push(`<line x1="${50 + x * 0.8}" y1="${y}" x2="${50 - x * 0.8}" y2="${y}"/>`);
  }
  return `<polyline points="${a.join(" ")}"/><polyline points="${b.join(" ")}"/>${rungs.join("")}`;
}

const ICONS = {
  chip: `<rect x="30" y="30" width="40" height="40" rx="5"/><rect x="41" y="41" width="18" height="18" rx="2"/>` +
    [40, 50, 60].map(v => `<path d="M${v} 18V30M${v} 70V82M18 ${v}H30M70 ${v}H82"/>`).join(""),
  headphones: `<path d="M22 62V52a28 28 0 0 1 56 0V62"/><rect x="15" y="58" width="15" height="25" rx="6"/><rect x="70" y="58" width="15" height="25" rx="6"/>`,
  helix: helix(),
  brain: `<path d="M50 20C42 13 28 16 27 28C17 30 14 44 21 51C14 59 20 74 33 72C36 82 47 83 50 76"/><path d="M50 20C58 13 72 16 73 28C83 30 86 44 79 51C86 59 80 74 67 72C64 82 53 83 50 76"/><path d="M50 20V76"/><path d="M27 28C33 30 37 35 37 41M21 51C28 50 33 53 35 58M33 72C34 66 39 63 44 63M73 28C67 30 63 35 63 41M79 51C72 50 67 53 65 58M67 72C66 66 61 63 56 63"/>`,
  heart: `<path d="M50 80C22 62 14 46 21 33C28 20 45 21 50 34C55 21 72 20 79 33C86 46 78 62 50 80Z"/>`,
  heartbeat: `<path d="M50 82C22 64 14 48 21 35C28 22 45 23 50 36C55 23 72 22 79 35C86 48 78 64 50 82Z"/><polyline points="24,52 38,52 43,42 50,64 56,46 60,52 76,52"/>`,
  pulse: `<polyline points="8,55 28,55 36,35 46,76 55,22 63,62 69,55 92,55"/>`,
  virus: `<circle cx="50" cy="50" r="17"/><circle cx="45" cy="46" r="3"/><circle cx="56" cy="54" r="2.5"/>` + ring(8, 17, 28, (x, y) => `<circle cx="${x}" cy="${y}" r="3.5"/>`),
  lungs: `<path d="M50 16V44M50 44C45 48 41 50 39 56M50 44C55 48 59 50 61 56"/><path d="M42 33C29 30 19 46 17 64C16 77 25 82 35 78C41 76 43 70 43 62V46"/><path d="M58 33C71 30 81 46 83 64C84 77 75 82 65 78C59 76 57 70 57 62V46"/>`,
  drop: `<path d="M50 14C50 14 26 43 26 60a24 24 0 0 0 48 0C74 43 50 14 50 14Z"/><path d="M38 62a12 12 0 0 0 10 12"/>`,
  microscope: `<polygon points="41,20 52,15 66,47 55,52"/><path d="M60 50L63 58M38 66H74M55 52C40 56 34 70 42 78M28 84H76M42 78V84"/>`,
  flask: `<path d="M41 15H59M45 15V40L26 77a5 5 0 0 0 4.5 7H69.5a5 5 0 0 0 4.5-7L55 40V15"/><path d="M33 64H67"/><circle cx="45" cy="72" r="2.5"/><circle cx="56" cy="76" r="2"/>`,
  chart: `<path d="M18 16V82H86"/><rect x="28" y="58" width="9" height="24"/><rect x="45" y="46" width="9" height="36"/><rect x="62" y="34" width="9" height="48"/><polyline points="24,48 41,38 58,28 80,16"/>`,
  curve: `<path d="M12 80H88"/><path d="M14 78C34 78 38 22 50 22C62 22 66 78 86 78"/><path d="M50 22V80" stroke-dasharray="3 5"/><path d="M32 66V80M68 66V80" stroke-dasharray="3 5"/>`,
  atom: `<circle cx="50" cy="50" r="5"/><ellipse cx="50" cy="50" rx="36" ry="13"/><ellipse cx="50" cy="50" rx="36" ry="13" transform="rotate(60 50 50)"/><ellipse cx="50" cy="50" rx="36" ry="13" transform="rotate(120 50 50)"/>`,
  cosmos: `<circle cx="50" cy="52" r="16"/><ellipse cx="50" cy="52" rx="34" ry="9" transform="rotate(-18 50 52)"/><path d="M18 18l2 5 5 2-5 2-2 5-2-5-5-2 5-2zM80 74l1.5 4 4 1.5-4 1.5-1.5 4-1.5-4-4-1.5 4-1.5zM82 20v6M79 23h6"/>`,
  star: `<rect x="27" y="27" width="46" height="46"/><rect x="27" y="27" width="46" height="46" transform="rotate(45 50 50)"/><circle cx="50" cy="50" r="10"/>`,
  moon: `<path d="M58 16A35 35 0 1 0 84 64A28 28 0 1 1 58 16Z"/><path d="M76 24l1.8 4.5 4.5 1.8-4.5 1.8-1.8 4.5-1.8-4.5-4.5-1.8 4.5-1.8z"/>`,
  arch: `<path d="M27 84V48C27 33 39 25 50 15C61 25 73 33 73 48V84"/><path d="M38 84V55C38 45 44 39 50 34C56 39 62 45 62 55V84"/><path d="M18 84H82"/>`,
  book: `<path d="M50 28C40 22 26 22 15 26V76C26 72 40 72 50 78C60 72 74 72 85 76V26C74 22 60 22 50 28Z"/><path d="M50 28V78M24 38C31 36 37 36 42 38M24 48C31 46 37 46 42 48M58 38C63 36 69 36 76 38M58 48C63 46 69 46 76 48"/>`,
  quill: `<path d="M80 14C52 18 33 41 28 72C45 66 67 49 80 14Z"/><path d="M28 72L19 85M40 58L58 38M34 66L50 52M16 87H52"/>`,
  tree: `<path d="M50 86V64M50 64L30 44M50 64L70 44M30 44L20 24M30 44L40 24M70 44L60 24M70 44L80 24"/><circle cx="20" cy="20" r="3.5"/><circle cx="40" cy="20" r="3.5"/><circle cx="60" cy="20" r="3.5"/><circle cx="80" cy="20" r="3.5"/>`,
  bird: `<path d="M16 56C28 47 40 46 49 52C55 38 69 29 85 29C77 35 73 43 71 52C67 67 54 75 38 73L25 81L29 69C22 65 18 61 16 56Z"/><path d="M49 52C53 58 60 60 66 58"/><circle cx="76" cy="36" r="1.5"/>`,
  bee: `<ellipse cx="50" cy="60" rx="20" ry="13"/><path d="M44 47.6V72.4M54 47.3V72.7M30 60H23"/><ellipse cx="42" cy="34" rx="9" ry="14" transform="rotate(-20 42 34)"/><ellipse cx="59" cy="34" rx="9" ry="14" transform="rotate(20 59 34)"/><circle cx="76" cy="57" r="6"/><path d="M78 51C80 44 84 41 88 41"/>`,
  mosquito: `<ellipse cx="44" cy="56" rx="20" ry="5" transform="rotate(-24 44 56)"/><circle cx="68" cy="44" r="5"/><path d="M72 41L90 30"/><ellipse cx="52" cy="34" rx="7" ry="18" transform="rotate(-50 52 34)"/><ellipse cx="44" cy="36" rx="6" ry="16" transform="rotate(-70 44 36)"/><path d="M60 50L66 70L62 86M56 52L54 72L46 86M62 48L78 62L84 80"/>`,
  fish: `<path d="M12 50C28 30 60 30 74 50C60 70 28 70 12 50Z"/><path d="M74 50L90 37V63Z"/><circle cx="26" cy="47" r="2"/><path d="M40 40C44 46 44 54 40 60"/>`,
  cell: `<path d="M50 16C70 15 85 30 84 50C83 70 70 85 50 84C30 83 15 70 16 50C17 30 30 17 50 16Z"/><circle cx="45" cy="45" r="11"/><circle cx="45" cy="45" r="3"/><ellipse cx="66" cy="62" rx="7" ry="4" transform="rotate(30 66 62)"/><circle cx="34" cy="68" r="2.5"/><circle cx="66" cy="36" r="2.5"/>`,
  scales: `<path d="M50 20V82M34 84H66M20 30H80"/><circle cx="50" cy="17" r="3"/><path d="M22 30L12 56M22 30L32 56M78 30L68 56M78 30L88 56"/><path d="M10 56C12 66 32 66 34 56ZM66 56C68 66 88 66 90 56Z"/>`,
  hourglass: `<path d="M28 14H72M28 86H72"/><path d="M33 14C33 38 50 44 50 50C50 56 33 62 33 86M67 14C67 38 50 44 50 50C50 56 67 62 67 86"/><path d="M41 30H59C56 37 52 40 50 42C48 40 44 37 41 30Z"/><path d="M40 80C44 72 56 72 60 80Z"/>`,
  chain: `<rect x="14" y="38" width="40" height="22" rx="11" transform="rotate(-35 34 49)"/><rect x="46" y="40" width="40" height="22" rx="11" transform="rotate(-35 66 51)"/>`,
  cycle: `<path d="M76 42A27 27 0 0 0 27 35"/><path d="M27 23V35H39"/><path d="M24 58A27 27 0 0 0 73 65"/><path d="M73 77V65H61"/>`,
  steps: `<path d="M14 84H32V68H48V52H64V36H82"/><circle cx="82" cy="20" r="5"/><path d="M82 8V11M93 20H90M74 12l2 2M90 12l-2 2"/>`,
  compass: `<circle cx="50" cy="17" r="5"/><path d="M47 22L28 84M53 22L72 84M34 64C44 70 56 70 66 64M50 12V6"/>`,
  sun: `<circle cx="50" cy="50" r="15"/>` + ring(12, 23, 34),
  rings: `<circle cx="50" cy="50" r="6"/><circle cx="50" cy="50" r="16"/><circle cx="50" cy="50" r="26" stroke-dasharray="2 5"/><circle cx="50" cy="50" r="37" stroke-dasharray="1 7"/>`,
  burst: `<circle cx="50" cy="50" r="4"/>` + ring(16, 12, 30) +
    `<circle cx="20" cy="22" r="1.8"/><circle cx="84" cy="30" r="1.8"/><circle cx="78" cy="84" r="1.8"/><circle cx="16" cy="74" r="1.8"/>`,
  key: `<circle cx="32" cy="50" r="14"/><circle cx="32" cy="50" r="4"/><path d="M46 50H86M74 50V62M84 50V59"/>`,
  head: `<path d="M58 86V71C70 71 75 66 75 57L82 51L75 46C77 27 64 15 47 15C30 15 20 28 20 43C20 53 26 61 33 65V86"/><circle cx="47" cy="40" r="8"/><path d="M47 28V32M47 48V52M35 40H39M55 40H59M39 32l3 3M55 32l-3 3M39 48l3-3M55 48l-3-3"/>`,
  eye: `<path d="M10 50C25 27 75 27 90 50C75 73 25 73 10 50Z"/><circle cx="50" cy="50" r="13"/><circle cx="50" cy="50" r="4"/>`,
  bulb: `<path d="M38 64C30 58 25 50 25 40a25 25 0 0 1 50 0C75 50 70 58 62 64V71H38Z"/><path d="M40 77H60M44 84H56M44 58L50 44L56 58"/>`,
  question: `<path d="M34 34C34 22 42 15 51 15C62 15 70 22 70 32C70 45 52 46 52 61V65"/><circle cx="52" cy="80" r="3"/>`,
  robot: `<rect x="28" y="30" width="44" height="34" rx="7"/><circle cx="41" cy="45" r="4"/><circle cx="59" cy="45" r="4"/><path d="M42 55H58M50 30V20M22 42V54M78 42V54M32 86C32 74 68 74 68 86"/><circle cx="50" cy="16" r="4"/>`,
  coin: `<circle cx="50" cy="50" r="32"/><circle cx="50" cy="50" r="24"/><path d="M42 36C46 34 50 37 50 42V60C50 66 56 68 60 64M42 46H58"/>`,
  sunrise: `<path d="M10 68H90M28 68A22 22 0 0 1 72 68M24 78H76M36 87H64M50 26V36M24 40l7 7M76 40l-7 7M14 56h8M86 56h-8"/>`,
  rain: `<path d="M30 58a14 14 0 0 1 2-28a19 19 0 0 1 35-4a15 15 0 0 1 3 32Z"/><path d="M34 68L30 78M48 68L44 82M62 68L58 78"/>`,
  wind: `<path d="M12 38H60a10 10 0 1 0-10-10M12 54H74a10 10 0 1 1-10 10M12 70H42"/>`,
  waves: `<path d="M10 36C20 28 30 28 40 36S60 44 70 36S88 28 90 34M10 52C20 44 30 44 40 52S60 60 70 52S88 44 90 50M10 68C20 60 30 60 40 68S60 76 70 68S88 60 90 66"/>`,
  flag: `<path d="M28 14V88M28 20C44 13 58 28 78 20V52C58 60 44 45 28 52"/><circle cx="51" cy="36" r="8"/>`,
  house: `<path d="M22 50L50 26L78 50M30 43V74H70V43M45 74V60H55V74"/><path d="M14 84C22 80 30 88 38 84S54 80 62 84S78 88 86 84"/>`,
  path: `<path d="M28 90C28 72 70 68 66 52C62 38 36 40 38 26C39 20 45 15 52 13"/><circle cx="56" cy="11" r="3"/><circle cx="28" cy="90" r="0.5"/>`,
  stethoscope: `<path d="M30 14V38a16 16 0 0 0 32 0V14M25 14H35M57 14H67M46 54V66a14 14 0 0 0 28 0V59"/><circle cx="74" cy="52" r="7"/>`,
  cap: `<path d="M50 24L88 40L50 56L12 40Z"/><path d="M28 47V64C28 73 72 73 72 64V47M88 40V62"/>`,
  people: `<circle cx="36" cy="32" r="9"/><circle cx="66" cy="36" r="8"/><path d="M18 82C18 60 54 60 54 82M50 84C52 66 82 66 82 84"/>`,
  butterfly: `<path d="M50 32C41 15 16 16 18 36C20 50 40 52 50 48C40 54 24 62 30 76C36 86 48 75 50 60C52 75 64 86 70 76C76 62 60 54 50 48C60 52 80 50 82 36C84 16 59 15 50 32Z"/><path d="M50 28V72M50 28C48 20 44 16 40 14M50 28C52 20 56 16 60 14"/>`,
  infinity: `<path d="M50 50C40 35 16 35 16 50C16 65 40 65 50 50C60 35 84 35 84 50C84 65 60 65 50 50Z"/>`,
  speech: `<path d="M20 22H80a7 7 0 0 1 7 7V60a7 7 0 0 1-7 7H42L26 80V67H20a7 7 0 0 1-7-7V29a7 7 0 0 1 7-7Z"/><path d="M28 38H72M28 50H58"/>`,
  mic: `<rect x="39" y="12" width="22" height="44" rx="11"/><path d="M28 44a22 22 0 0 0 44 0M50 66V82M36 84H64"/>`,
  flame: `<path d="M50 88C30 88 21 72 25 57C29 45 40 41 40 25C53 31 59 44 57 56C61 52 63 47 63 41C74 51 78 64 74 74C70 83 61 88 50 88Z"/><path d="M50 88C42 88 39 81 41 75C43 69 49 67 50 60C56 66 59 72 57 79C56 84 53 88 50 88Z"/>`,
  crown: `<path d="M20 70L15 32L34 50L50 22L66 50L85 32L80 70Z"/><path d="M20 80H80"/>`,
  shield: `<path d="M50 13L80 25V47C80 66 67 80 50 87C33 80 20 66 20 47V25Z"/><path d="M37 50L47 60L64 39"/>`,
  pill: `<g transform="rotate(-35 50 50)"><rect x="20" y="37" width="60" height="26" rx="13"/><path d="M50 37V63"/></g><circle cx="26" cy="78" r="2"/><circle cx="76" cy="22" r="2"/>`,
  scan: `<path d="M14 30V14H30M70 14H86V30M86 70V86H70M30 86H14V70"/><circle cx="50" cy="50" r="18"/><path d="M8 50H92" stroke-dasharray="4 4"/>`,
  apple: `<path d="M50 34C40 26 21 28 21 50C21 70 36 87 50 80C64 87 79 70 79 50C79 28 60 26 50 34Z"/><path d="M50 34C50 26 52 20 57 15M53 26C58 20 66 20 70 22C66 28 58 29 53 26Z"/>`,
};
