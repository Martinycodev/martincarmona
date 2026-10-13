
const D = __DATA__;
const P = D.P;
const needleAt = __NEEDLE__;
const wheelAt = __WHEEL__;
const DIAL = D.dial, REG_C = D.regC, WHEEL = D.wheel;
const stage = document.querySelector(".bm-stage");
const svg = stage.querySelector("svg");
const $ = (name) => svg.querySelector(`[data-part="${name}"]`);
const readout = document.querySelector("[data-readout]");
const status = stage.querySelector("[data-status]");
const still = matchMedia("(prefers-reduced-motion: reduce)");
const NS = "http://www.w3.org/2000/svg";
const f1 = (v) => Math.round(v * 10) / 10;
const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));
const SLOW = 1 / 15; // el mecanismo se ve 15 veces más lento que en el tractor
const [SX, SZ] = D.shaft;
const Y = (z) => -z;

// ── Piezas que se mueven dentro del corte (coordenadas de cara: x, −z).
const circlePath = (cx, cy, r, n = 28, ccw = false) => {
  const pts = Array.from({ length: n }, (_, i) => { const a = ((ccw ? -1 : 1) * i * Math.PI * 2) / n; return `${f1(cx + r * Math.cos(a))} ${f1(cy + r * Math.sin(a))}`; });
  return `M${pts.join("L")}Z`;
};
const crank = $("crank");
{
  const teeth = [];
  for (let i = 0; i < 12; i++) {
    const a0 = (i / 12) * Math.PI * 2;
    const r = i % 2 ? 9.5 : 12;
    teeth.push([SX + r * Math.sin(a0 - 0.13), Y(SZ) - r * Math.cos(a0 - 0.13)], [SX + r * Math.sin(a0 + 0.13), Y(SZ) - r * Math.cos(a0 + 0.13)]);
  }
  const arc = (r, a0, a1, n = 24) => Array.from({ length: n + 1 }, (_, i) => { const a = a0 + ((a1 - a0) * i) / n; return [SX + r * Math.sin(a), Y(SZ) - r * Math.cos(a)]; });
  const outline = arc(31, 0, Math.PI * 2, 72).slice(0, -1).map(([x, y]) => `${f1(x)} ${f1(y)}`).join("L");
  const pinZ = Y(SZ + D.pump.e);
  crank.innerHTML =
    `<path class="bm-part" d="M${outline}Z"/>` +
    `<path class="bm-void" d="${[[-0.62, 21], [0, 22], [0.62, 21]].map(([a, r]) => circlePath(SX + r * Math.sin(Math.PI + a), Y(SZ) - r * Math.cos(Math.PI + a), 3.4, 20)).join("")}"/>` +
    `<path class="bm-ink" d="${[[-0.62, 21], [0, 22], [0.62, 21]].map(([a, r]) => circlePath(SX + r * Math.sin(Math.PI + a), Y(SZ) - r * Math.cos(Math.PI + a), 3.4, 20)).join("")}"/>` +
    `<path class="bm-insert" d="M${teeth.map(([x, y]) => `${f1(x)} ${f1(y)}`).join("L")}Z"/>` +
    `<circle class="bm-void" cx="${SX}" cy="${Y(SZ)}" r="2.4"/>` +
    `<circle class="bm-part" cx="${SX}" cy="${pinZ}" r="9.6"/><circle class="bm-ink" cx="${SX}" cy="${pinZ}" r="3"/>`;
}
const rod = $("rod");
const rodG = document.createElementNS(NS, "g");
rod.replaceWith(rodG);
const piston = $("piston");
const valves = $("valves");
const membrane = $("membrane");
const washer = document.createElementNS(NS, "g");
membrane.after(washer);
const under = document.createElementNS(NS, "path");
under.setAttribute("class", "bm-under");
svg.querySelector('[data-part="chamber-water"]').before(under);
const chamberWater = $("chamber-water");
const chamberMembrane = $("chamber-membrane");
const flowIn = $("flow-in");
const flowOut = $("flow-out");
const needle = $("needle");
const wheelPtr = $("wheel");
const spray = $("spray");


function drawRod(theta) {
  const { pin, wrist } = pistonAt(theta);
  const big = [SX + pin[0], Y(SZ + pin[1])];
  const small = [SX, Y(SZ + wrist)];
  const dx = small[0] - big[0], dy = small[1] - big[1];
  const len = Math.hypot(dx, dy);
  const nx = -dy / len, ny = dx / len;
  const shank = [[big[0] + nx * 8, big[1] + ny * 8], [small[0] + nx * 5, small[1] + ny * 5], [small[0] - nx * 5, small[1] - ny * 5], [big[0] - nx * 8, big[1] - ny * 8]];
  const sh = `M${shank.map(([x, y]) => `${f1(x)} ${f1(y)}`).join("L")}Z`;
  const outer = circlePath(big[0], big[1], 16, 36) + circlePath(small[0], small[1], 9, 24) + sh;
  rodG.innerHTML = `<path class="bm-part" d="${outer}"/><path class="bm-part" style="stroke:none" d="${outer}"/>` +
    `<path class="bm-void" d="${circlePath(big[0], big[1], 10.4, 32)}"/><path class="bm-ink" d="${circlePath(big[0], big[1], 10.4, 32)}"/>` +
    `<path class="bm-ink" d="M${f1(big[0] + nx * 3)} ${f1(big[1] + ny * 3)}L${f1(small[0] + nx * 2)} ${f1(small[1] + ny * 2)}M${f1(big[0] - nx * 3)} ${f1(big[1] - ny * 3)}L${f1(small[0] - nx * 2)} ${f1(small[1] - ny * 2)}"/>`;
  // Hace falta que el bulón del pie de biela se vea a través del pistón.
  return wrist;
}

function drawPiston(wrist) {
  const top = SZ + wrist + 16;
  const zc = top + 5.2;
  const b = top - 32;
  const wall = `M-26 ${Y(b)}L-26 ${Y(top)}L26 ${Y(top)}L26 ${Y(b)}L21 ${Y(b)}L21 ${Y(top - 8)}L-21 ${Y(top - 8)}L-21 ${Y(b)}Z`;
  const grooves = [3, 6.5, 10].map((g) => `M-26 ${f1(Y(top - g))}L-23.6 ${f1(Y(top - g))}M23.6 ${f1(Y(top - g))}L26 ${f1(Y(top - g))}`).join("");
  piston.innerHTML =
    `<path class="bm-part" data-lit d="${wall}"/>` +
    `<path class="bm-ink" d="${grooves}"/>` +
    `<path class="bm-part" d="M-21 ${f1(Y(top - 16 + 7))}L-14 ${f1(Y(top - 16 + 7))}L-14 ${f1(Y(top - 16 - 7))}L-21 ${f1(Y(top - 16 - 7))}ZM21 ${f1(Y(top - 16 + 7))}L14 ${f1(Y(top - 16 + 7))}L14 ${f1(Y(top - 16 - 7))}L21 ${f1(Y(top - 16 - 7))}Z"/>` +
    `<circle class="bm-part" cx="0" cy="${f1(Y(top - 16))}" r="5"/>` +
    `<path class="bm-part" d="M-24 ${f1(Y(top))}L24 ${f1(Y(top))}L24 ${f1(Y(top + 4))}L-24 ${f1(Y(top + 4))}Z"/>` +
    `<path class="bm-ink" d="M-1.6 ${f1(Y(top + 4))}L-1.6 ${f1(Y(top - 8))}M1.6 ${f1(Y(top + 4))}L1.6 ${f1(Y(top - 8))}"/>`;
  // Membrana: borde pinzado entre cuerpo y culata, tramo libre en S y zona plana sobre el platillo.
  const half = 1.2;
  const curve = (off) => {
    const pts = [];
    const span = (xa, za, xb, zb, n) => {
      for (let i = 0; i <= n; i++) {
        const t = i / n;
        const s = t * t * (3 - 2 * t);
        pts.push([xa + (xb - xa) * t, za + (zb - za) * s + off]);
      }
    };
    pts.push([-66, 184 + off]);
    span(-50, 184, -24, zc, 14);
    span(24, zc, 50, 184, 14);
    pts.push([66, 184 + off]);
    return pts;
  };
  const upper = curve(half), lower = curve(-half).reverse();
  washer.innerHTML =
    `<path class="bm-part" d="M-14 ${f1(Y(zc + 1.2))}L14 ${f1(Y(zc + 1.2))}L14 ${f1(Y(zc + 3.6))}L-14 ${f1(Y(zc + 3.6))}Z"/>` +
    `<path class="bm-part" d="M-5 ${f1(Y(zc + 3.6))}L5 ${f1(Y(zc + 3.6))}L5 ${f1(Y(zc + 7))}L-5 ${f1(Y(zc + 7))}Z"/>`;
  membrane.setAttribute("d", `M${[...upper, ...lower].map(([x, z]) => `${f1(x)} ${f1(Y(z))}`).join("L")}Z`);
  under.setAttribute("d", `M${lower.map(([x, z]) => `${f1(x)} ${f1(Y(z))}`).join("L")}L-66 ${Y(170)}L66 ${Y(170)}Z`.replace(/^M([^L]*)L/, "M$1L"));
  return top;
}

function spring(x, z0, z1, coils = 6, r = 6, wire = 1.1) {
  const out = [];
  const pitch = (z1 - z0) / coils;
  for (let i = 0; i < coils; i++) {
    out.push(`<circle class="bm-wire" cx="${f1(x - r)}" cy="${f1(Y(z0 + pitch * (i + 0.25)))}" r="${wire}"/>`);
    out.push(`<circle class="bm-wire" cx="${f1(x + r)}" cy="${f1(Y(z0 + pitch * (i + 0.75)))}" r="${wire}"/>`);
  }
  return out.join("");
}
function valve(x, lift) {
  const z = 210.5 + lift;
  return (
    spring(x, z + 3, 236) +
    `<path class="bm-part" d="M${x - 10} ${f1(Y(z))}L${x + 10} ${f1(Y(z))}L${x + 10} ${f1(Y(z + 3))}L${x - 10} ${f1(Y(z + 3))}Z"/>` +
    `<path class="bm-part" d="M${x - 1.6} ${f1(Y(z + 3))}L${x + 1.6} ${f1(Y(z + 3))}L${x + 1.6} ${f1(Y(z + 14))}L${x - 1.6} ${f1(Y(z + 14))}Z"/>`
  );
}

// Calderín: el agua sube por debajo de la membrana a medida que se comprime el aire.
function drawChamber(share) {
  const { x: cx, z: cz } = D.chamber;
  const ri = D.chamber.r - D.chamber.wall;
  const h = clamp((share * Math.PI * ri * ri - (Math.PI * ri * ri) / 2) / ((4 / 3) * ri), -ri + 4, ri - 4);
  const top = [];
  for (let i = 0; i <= 24; i++) {
    const x = -ri + (2 * ri * i) / 24;
    top.push([cx + x, cz + h * (1 - (x / ri) ** 2)]);
  }
  const bottom = [];
  for (let i = 1; i < 36; i++) {
    const a = (i / 36) * Math.PI;
    bottom.push([cx + ri * Math.cos(a), cz - ri * Math.sin(a)]);
  }
  chamberWater.setAttribute("d", `M${[...top, ...bottom].map(([x, z]) => `${f1(x)} ${f1(Y(z))}`).join("L")}Z`);
  const band = (off) => top.map(([x, z]) => [x, z + off]);
  const rim = (off, dir) => (dir > 0 ? [[cx - ri - 6, cz + off], ...band(off), [cx + ri + 6, cz + off]] : [[cx + ri + 6, cz + off], ...band(off).reverse(), [cx - ri - 6, cz + off]]);
  chamberMembrane.setAttribute("d", `M${[...rim(1, 1), ...rim(-1, -1)].map(([x, z]) => `${f1(x)} ${f1(Y(z))}`).join("L")}Z`);
}

// Gotas que avanzan por los canales cuando su válvula está abierta.
const DROPS = 9;
function makeDrops(group) {
  const list = [];
  for (let i = 0; i < DROPS; i++) {
    const c = document.createElementNS(NS, "circle");
    c.setAttribute("r", "1.3");
    group.appendChild(c);
    list.push({ el: c, s: i / DROPS });
  }
  return list;
}
const along = (path, t) => {
  const segs = [];
  let total = 0;
  for (let i = 0; i + 1 < path.length; i++) { const l = Math.hypot(path[i + 1][0] - path[i][0], path[i + 1][1] - path[i][1]); segs.push(l); total += l; }
  let d = t * total;
  for (let i = 0; i < segs.length; i++) {
    if (d <= segs[i]) { const u = d / segs[i]; return [path[i][0] + (path[i + 1][0] - path[i][0]) * u, path[i][1] + (path[i + 1][1] - path[i][1]) * u]; }
    d -= segs[i];
  }
  return path[path.length - 1];
};
const dropsIn = makeDrops(flowIn);
const dropsOut = makeDrops(flowOut);
function drawDrops(list, path, share) {
  for (const d of list) {
    const [x, z] = along(path, d.s);
    d.el.setAttribute("cx", f1(x));
    d.el.setAttribute("cy", f1(Y(z)));
    const edge = Math.min(d.s, 1 - d.s) * 6;
    d.el.setAttribute("opacity", f1(Math.min(1, edge) * share * 0.9 * 100) / 100);
  }
}

// Pulverización: gotas en un cono que sale de cada boquilla.
const SPRAY = [];
let seed = 7;
const rand = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
for (const tip of D.tips) {
  for (let i = 0; i < 56; i++) {
    const c = document.createElementNS(NS, "circle");
    spray.appendChild(c);
    SPRAY.push({ el: c, tip, s: rand(), a: rand() * Math.PI * 2, w: 0.35 + rand() * 0.65, r: 0.6 + rand() * 0.8 });
  }
}
function drawSpray(reach, dt, calm) {
  for (const p of SPRAY) {
    if (!calm) p.s = (p.s + dt * 0.9) % 1;
    const s = p.s;
    const dist = 6 + s * 70 * reach;
    const spread = Math.tan(0.6) * dist * p.w;
    const pt = [p.tip[0] + dist, p.tip[1] + Math.cos(p.a) * spread, p.tip[2] + Math.sin(p.a) * spread - s * s * 8];
    const [x, y] = iso(pt, P);
    p.el.setAttribute("cx", f1(x));
    p.el.setAttribute("cy", f1(y));
    p.el.setAttribute("r", p.r);
    const fade = Math.min(1, s * 8) * (1 - s);
    p.el.setAttribute("opacity", f1(fade * Math.min(1, reach * 1.4) * 100) / 100);
  }
}

// ── Estado
const state = {
  rpm: { x: 0, v: 0 },
  set: { x: 15, v: 0 },
  bar: { x: 0, v: 0 },
  share: { x: 0, v: 0 },
  target: { rpm: 540, set: 15 },
  open: true,
  theta: 0,
  ls: 0,
  ld: 0,
};
const settleTo = (s, target, dt, seconds, calm) => {
  if (calm) { s.x = target; s.v = 0; return false; }
  const w = 1 / seconds;
  for (let i = 0; i < 4; i++) { const h = dt / 4; s.v += (w * w * (target - s.x) - 2 * w * s.v) * h; s.x += s.v * h; }
  if (Math.abs(target - s.x) < 1e-4 && Math.abs(s.v) < 1e-4) { s.x = target; s.v = 0; }
  return s.x !== target;
};

let frame = 0, last = 0, visible = false, touring = !still.matches, clock = 0, idle = 0;
const TOUR = [
  [0, 540, 15, true],
  [7, 540, 22, true],
  [12, 540, 10, true],
  [17, 540, 15, false],
  [22, 540, 15, true],
  [26, 120, 15, true],
  [31, 540, 15, true],
];
const TOUR_LEN = 36;
function tourAt(t) {
  const u = t % TOUR_LEN;
  let pick = TOUR[0];
  for (const step of TOUR) if (u >= step[0]) pick = step;
  return pick;
}
let lastWords = "";
function say() {
  const rpm = Math.round(state.rpm.x);
  const flows = flowsOf(state.rpm.x, state.bar.x, state.open);
  const text = `${rpm} rpm · ${Math.round(flows.pump)} L/min · ${f1(state.bar.x).toFixed(1).replace(".", ",")} bar · ${state.open ? `${Math.round(flows.nozzles)} a boquillas` : "boquillas cerradas"}`;
  if (readout.textContent !== text) readout.textContent = text;
}
function describe() {
  const flows = flowsOf(state.target.rpm, pressureOf(state.target.rpm, state.target.set, state.open), state.open);
  const words = `${state.target.rpm} rpm, regulador a ${state.target.set} bar, ${state.open ? `${Math.round(flows.nozzles)} litros por minuto a las boquillas y ${Math.round(flows.back)} de retorno a la cuba` : "boquillas cerradas, todo el caudal vuelve a la cuba"}`;
  if (words !== lastWords) { lastWords = words; status.textContent = words; stage.setAttribute("aria-valuetext", words); }
}

function draw(dt, calm) {
  const theta = calm ? 1.9 : state.theta;
  const wrist = drawRod(theta);
  drawPiston(wrist);
  crank.setAttribute("transform", `rotate(${f1((theta * 180) / Math.PI)} ${SX} ${Y(SZ)})`);
  const run = clamp(state.rpm.x / 60, 0, 1);
  const v = speedAt(theta) / D.pump.e;
  const wantS = clamp(-v, 0, 1) * run, wantD = clamp(v, 0, 1) * run;
  state.ls += (wantS - state.ls) * (calm ? 1 : 1 - Math.exp(-dt / 0.05));
  state.ld += (wantD - state.ld) * (calm ? 1 : 1 - Math.exp(-dt / 0.05));
  valves.innerHTML = valve(D.suctionX, state.ls * 4) + valve(D.deliveryX, state.ld * 4);
  const speed = Math.max(0.15, state.rpm.x / 540);
  if (!calm) {
    for (const d of dropsIn) d.s = (d.s + dt * 0.9 * state.ls * speed / SLOW * 0.07) % 1;
    for (const d of dropsOut) d.s = (d.s + dt * 0.9 * state.ld * speed / SLOW * 0.07) % 1;
  }
  drawDrops(dropsIn, D.flowIn, calm ? 0.6 : state.ls);
  drawDrops(dropsOut, D.flowOut, calm ? 0.6 : state.ld);
  drawChamber(state.share.x);
  needle.setAttribute("d", needleAt(state.bar.x));
  wheelPtr.setAttribute("d", wheelAt(state.set.x));
  const flows = flowsOf(state.rpm.x, state.bar.x, state.open);
  drawSpray(Math.sqrt(flows.nozzles / nozzleFlow(15)), dt, calm);
  say();
}

function tick(now) {
  const dt = last ? Math.min((now - last) / 1000, 1 / 30) : 1 / 60;
  last = now;
  const calm = still.matches;
  if (touring) {
    clock += dt;
    const [, rpm, set, open] = tourAt(clock);
    state.target.rpm = rpm; state.target.set = set;
    if (open !== state.open) state.open = open;
    describe();
  }
  settleTo(state.rpm, state.target.rpm, dt, 0.5, calm);
  settleTo(state.set, state.target.set, dt, 0.35, calm);
  const want = pressureOf(state.rpm.x, state.set.x, state.open);
  settleTo(state.bar, want, dt, 0.25, calm);
  // El calderín sigue a la presión, más un pequeño latido por cada embolada de las cuatro membranas.
  const beat = state.rpm.x > 1 ? 0.012 * Math.sin(state.theta * 4) : 0;
  settleTo(state.share, chamberShare(state.bar.x) + beat, dt, 0.12, calm);
  if (!calm) state.theta = (state.theta + (state.rpm.x / 60) * Math.PI * 2 * SLOW * dt) % (Math.PI * 2);
  draw(dt, calm);
  frame = visible ? requestAnimationFrame(tick) : 0;
}
const run = () => { if (!frame && visible) { last = 0; frame = requestAnimationFrame(tick); } };
const steer = (rpm, set) => {
  touring = false;
  if (rpm !== null) state.target.rpm = Math.round(clamp(rpm, 0, 540) / 10) * 10;
  if (set !== null) state.target.set = Math.round(clamp(set, 5, 25));
  describe();
  clearTimeout(idle);
  idle = setTimeout(() => { touring = !still.matches; clock = 0; run(); }, 6000);
  run();
};
new IntersectionObserver((entries) => { visible = entries[entries.length - 1].isIntersecting; if (visible) run(); }, { rootMargin: "120px 0px" }).observe(stage);
if (still.matches) { touring = false; state.target.rpm = 540; }
stage.addEventListener("pointermove", (event) => {
  if (event.pointerType === "touch") return;
  const box = svg.getBoundingClientRect();
  const fx = clamp(((event.clientX - box.left) / box.width - 0.1) / 0.8, 0, 1);
  const fy = clamp(((event.clientY - box.top) / box.height - 0.1) / 0.8, 0, 1);
  steer(fx * 540, 25 - fy * 20);
});
stage.addEventListener("pointerdown", (event) => {
  if (event.pointerType !== "touch") return;
  const box = svg.getBoundingClientRect();
  steer(clamp(((event.clientX - box.left) / box.width - 0.1) / 0.8, 0, 1) * 540, 25 - clamp(((event.clientY - box.top) / box.height - 0.1) / 0.8, 0, 1) * 20);
});
stage.addEventListener("keydown", (event) => {
  const big = event.shiftKey;
  let rpm = null, set = null;
  if (event.key === "ArrowRight") rpm = state.target.rpm + (big ? 100 : 20);
  else if (event.key === "ArrowLeft") rpm = state.target.rpm - (big ? 100 : 20);
  else if (event.key === "ArrowUp") set = state.target.set + (big ? 5 : 1);
  else if (event.key === "ArrowDown") set = state.target.set - (big ? 5 : 1);
  else if (event.key === "Home") rpm = 0;
  else if (event.key === "End") rpm = 540;
  else if (event.key === " " || event.key === "Enter") { state.open = !state.open; }
  else return;
  event.preventDefault();
  steer(rpm, set);
});
stage.setAttribute("aria-valuetext", "");
describe();
draw(1 / 60, true);
