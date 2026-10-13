// Fig: la bomba de pistón-membrana de una atomizadora de 3000 L, seccionada como una maqueta de concesionario.
// node build.mjs [--light] [--audit]  →  public/anatomia/bomba-atomizadora(.html|-light.html)
import { readFileSync, writeFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import polygonClipping from "polygon-clipping";
import * as k from "../../../.claude/skills/anatomy/kit/iso-kit.mjs";
import * as G from "../../../.claude/skills/anatomy/kit/lathe.mjs";
import { tubePieces, tubeSvg } from "../../../.claude/skills/anatomy/kit/tube.mjs";
import * as A from "../../../.claude/skills/anatomy/kit/audit.mjs";
import { kitScript } from "../../../.claude/skills/anatomy/scripts/inline-kit.mjs";
import * as M from "./model.mjs";

const HERE = dirname(fileURLToPath(import.meta.url));
const OUT = join(HERE, "..", "..", "..", "public", "anatomia");
const THEME = process.argv.includes("--light") ? "light" : "dark";

const W = 700;
const H = 480;
const AZIMUTH = 56;
const ELEVATION = 24;

// ── Mundo (unidades ≈ mm). x a lo largo del corte, y hacia el espectador, z arriba. El corte es el plano y = 0.
const PLATE = { x: -290, y: -128, w: 600, d: 210, r: 10 };
const PLATE_H = 12;
const FOOT_H = 6;
const BACK = -60; // fondo del cuerpo de la bomba
const PY = -30; // plano de las bocas y tuberías exteriores
const SHAFT = [0, 184 - 21.2 - 70]; // la membrana queda a 184 en la mitad de la carrera // centro del cigüeñal (x, z)

const TANK = { x: -282, y: -122, w: 88, d: 122, r: 16 };
const TANK_H = 200;
const FILTER = [-101, PY]; // justo debajo de la boca de aspiración
const REG = { x: 168, y: -46, w: 58, d: 32, r: 4 };
const REG_Z = [204, 240];
const BAR = [176, 46]; // barra vertical del arco de boquillas
const LINE_X = 188; // tubo de boquillas
const NOZZLE_Z = [196, 150];

const P = k.fitProjection(
  [...k.boxCorners(PLATE, -PLATE_H - FOOT_H, 0), [112, 0, 336], [-282, -10, TANK_H + 4], [300, 46, 210], [168, -46, 282]],
  W,
  H,
  { pad: 26, azimuth: AZIMUTH, elevation: ELEVATION },
);
const V = G.viewOf(P);
const KS = G.scaleOf(P);
const at = (p) => k.iso(p, P);
const S = (paths, style) => k.solidSvg(paths, style);
const Ln = (d, style) => k.lineSvg(d, style);
const Dots = (points, style) => k.dotsSvg(points, style);
const path3 = (points, closed = false) => k.pathOf(points.map(at), closed);
const f2 = (v) => Math.round(v * 100) / 100;

const R = A.recorder(P);
const FRONT = A.box([-400, 0, -50], [400, 200, 500], { name: "front-cut" }); // lo que la maqueta ha perdido al cortarla
const record = R.solid;
const put = (name, ref, svg, shapes = [], { bias = 0, ...rest } = {}) => R.put({ name, at: ref, bias, svg, shapes, piece: "rig", ...rest });

function box(name, [x0, y0, z0], [x1, y1, z1], style = {}, { r = 0.8, steps = 3, bevel = 0.5, extra = "", inner = "", bias = 0, ground = false } = {}) {
  const plan = { x: x0, y: y0, w: x1 - x0, d: y1 - y0, r };
  const shape = record(A.slab(plan, z0, z1 - z0, { name, ...(ground ? { ground: true } : {}) }, steps));
  put(name, [(x0 + x1) / 2, (y0 + y1) / 2, (z0 + z1) / 2], S(k.slabOf(plan, z0, z1 - z0, P, steps, bevel), { tone: "mid", ...style, inner }) + extra, [shape], { bias });
  return shape;
}
function post(name, x, y, r, z0, z1, style = {}, { extra = "", inner = "", bias = 0, steps = 32, bevel = 0.4 } = {}) {
  const shape = record(A.cylinder(x, y, r, z0, z1 - z0, { name }));
  put(name, [x, y, (z0 + z1) / 2], S(k.cylinder(x, y, r, z0, z1 - z0, P, steps, bevel), { tone: "mid", ...style, inner }) + extra, [shape], { bias });
  return shape;
}
function turned(name, F, profile, style = {}, { extra = "", inner = "", bias = 0, owner, bevel = 0, meta = {} } = {}) {
  const paths = G.solidOf(profile, F, P, { bevel });
  const mid = (profile[0][0] + profile[profile.length - 1][0]) / 2;
  const shape = record(A.solid(F, profile, { name, owner, ...meta }));
  put(name, G.add3(F.o, G.mul3(F.a, mid)), S(paths, { tone: "mid", ...style, inner }) + extra, [shape], { bias });
  return shape;
}

// Medio sólido de revolución de eje vertical, cortado por y = 0 (se queda la mitad de atrás): tapas de válvula, calderín.
function halfRev(cx, profile, steps = 48) {
  const quads = [];
  for (let i = 0; i + 1 < profile.length; i++) {
    const [z0, r0] = profile[i];
    const [z1, r1] = profile[i + 1];
    const dz = z1 - z0;
    const dr = r1 - r0;
    const len = Math.hypot(dz, dr) || 1;
    for (let j = 0; j < steps; j++) {
      const a0 = Math.PI + (j / steps) * Math.PI;
      const a1 = Math.PI + ((j + 1) / steps) * Math.PI;
      const am = (a0 + a1) / 2;
      const n = [(dz / len) * Math.cos(am), (dz / len) * Math.sin(am), -dr / len];
      const pt = (r, z, a) => [cx + r * Math.cos(a), r * Math.sin(a), z];
      quads.push({ i, j, n, pts: [pt(r0, z0, a0), pt(r0, z0, a1), pt(r1, z1, a1), pt(r1, z1, a0)], flatTop: Math.abs(dz) < 1e-9 && dr < 0 });
    }
  }
  const seen = new Map();
  for (const q of quads) seen.set(`${q.i}:${q.j}`, G.dot3(q.n, V) > 1e-6 && Math.abs(q.pts[0][0] - q.pts[2][0]) + Math.abs(q.pts[0][2] - q.pts[2][2]) + Math.abs(q.pts[0][1] - q.pts[2][1]) > 1e-6);
  const fill = [];
  const shades = [[], [], [], []];
  const top = [];
  const outline = [];
  const crease = [];
  const oriented = (pts) => {
    const s = pts.map(at);
    const area = s.reduce((acc, [x, y], idx) => acc + x * s[(idx + 1) % s.length][1] - s[(idx + 1) % s.length][0] * y, 0);
    return area < 0 ? s.reverse() : s;
  };
  for (const q of quads) {
    if (!seen.get(`${q.i}:${q.j}`)) continue;
    const d = k.pathOf(oriented(q.pts), true);
    fill.push(d);
    if (q.flatTop) top.push(d);
    else shades[Math.min(3, G.toneOf(q.n, P))].push(d);
    const nb = [
      [`${q.i}:${q.j - 1}`, [q.pts[0], q.pts[3]], "side"],
      [`${q.i}:${q.j + 1}`, [q.pts[1], q.pts[2]], "side"],
      [`${q.i - 1}:${q.j}`, [q.pts[0], q.pts[1]], "ring"],
      [`${q.i + 1}:${q.j}`, [q.pts[3], q.pts[2]], "ring"],
    ];
    for (const [key, edge, kind] of nb) {
      const other = seen.get(key);
      if (!other) outline.push(path3(edge));
      else if (kind === "ring") {
        const [i2] = key.split(":").map(Number);
        const lo = Math.min(q.i, i2);
        const a = profile[lo];
        const b = profile[lo + 1];
        const c = profile[lo + 2];
        if (!c) continue;
        const t1 = Math.atan2(b[0] - a[0], b[1] - a[1]);
        const t2 = Math.atan2(c[0] - b[0], c[1] - b[1]);
        if (Math.abs(t1 - t2) > 0.5 && i2 > q.i && !profile[lo + 1][2]) crease.push(path3(edge));
      }
    }
  }
  return { fill: fill.join(""), shades: shades.map((s) => s.join("")), top: top.join(""), outline: outline.join(""), crease: crease.join("") };
}
function halfRevSvg(paths, { tone = "mid", lit = false } = {}) {
  return `<g class="iso-solid" data-tone="${tone}"${lit ? " data-lit" : ""}><path class="iso-fill" d="${paths.fill}"/>${paths.shades
    .map((d, i) => (d ? `<path class="iso-shade" data-shade="${i}" d="${d}"/>` : ""))
    .join("")}${paths.top ? `<path class="iso-top" d="${paths.top}"/>` : ""}<path class="iso-line" data-tone="faint" d="${paths.crease}"/><path class="iso-line iso-edge" data-tone="${tone}" d="${paths.outline}"/></g>`;
}

// ── Banco
const back = [];
back.push(`<path class="iso-halo" d="${k.haloOf(PLATE, -PLATE_H - FOOT_H, PLATE_H, P)}"/>`);
const byDepth = (points) => [...points].sort((a, b) => k.depthOf([a[0], a[1], 0], P) - k.depthOf([b[0], b[1], 0], P));
for (const [x, y] of byDepth(k.corners(PLATE, 24))) {
  back.push(S(k.cylinder(x, y, 8, -PLATE_H - FOOT_H, 1.8, P, 28), { tone: "lo" }));
  back.push(S(k.cylinder(x, y, 6, -PLATE_H - FOOT_H + 1.8, FOOT_H - 1.8, P, 28), { tone: "mid" }));
}
back.push(S(k.slabOf(PLATE, -PLATE_H, PLATE_H, P, 8, 1.6), { tone: "mid", inner: Ln(k.sideSeam(PLATE, -5, P, 8), { tone: "faint" }) }));
back.push(Ln(k.planOutline(k.insetPlan(PLATE, 6), 0, P), { tone: "lo" }));
const plateScrews = k.corners(PLATE, 12);
back.push(Ln(plateScrews.map(([x, y]) => k.ring(x, y, 2.6, 0, P, 16)).join(""), { tone: "lo" }));
back.push(Dots(plateScrews.map(([x, y]) => at([x, y, 0])), { size: 0.55 }));
record(A.slab(PLATE, -PLATE_H, PLATE_H, { name: "stand.plate", ground: true }, 8));
const STRIP = { x: -150, y: PLATE.y + PLATE.d - 16, w: 300, d: 7, r: 1 };
back.push(S(k.slabOf(STRIP, 0, 0.6, P, 3), { tone: "lo", crease: "none" }));
{
  const t = k.topTicks(STRIP.x + 4, STRIP.x + STRIP.w - 4, 5, 5, STRIP.y, 0.6, [2.2, 4], P);
  back.push(Ln(t.minor, { tone: "lo" }), Ln(t.major, { tone: "mid" }));
  back.push(Dots([at([STRIP.x + 2, STRIP.y + 3.5, 0.6]), at([STRIP.x + STRIP.w - 2, STRIP.y + 3.5, 0.6])], { size: 0.45 }));
}
const LABEL = { x: 214, y: PLATE.y + PLATE.d - 40, w: 64, d: 22, r: 1.5 };
back.push(S(k.slabOf(LABEL, 0, 0.8, P, 4), { tone: "lo", crease: "none" }));
back.push(Ln(k.planOutline(k.insetPlan(LABEL, 2), 0.8, P, 4), { tone: "faint" }));
back.push(k.faceTextSvg(k.topMatrix([LABEL.x + 6, LABEL.y + 9.5], 0.8, P, "x"), "3000 L · 540", { size: 5.2, tone: "lo" }));
back.push(Ln(k.lineOnTop([LABEL.x + 6, LABEL.y + 15], [LABEL.x + 44, LABEL.y + 15], 0.8, P) + k.lineOnTop([LABEL.x + 6, LABEL.y + 18], [LABEL.x + 30, LABEL.y + 18], 0.8, P), { tone: "faint", free: true }));
back.push(Dots([at([LABEL.x + 3, LABEL.y + 3, 0.8]), at([LABEL.x + LABEL.w - 3, LABEL.y + LABEL.d - 3, 0.8])], { size: 0.45 }));

// ── Cuba (recortada): polietileno, visor de nivel en la cara delantera.
const TANK_FRONT = TANK.y + TANK.d;
box("stand.tank", [TANK.x, TANK.y, 0], [TANK.x + TANK.w, TANK.y + TANK.d, TANK_H], { tone: "mid" }, {
  r: TANK.r,
  steps: 8,
  bevel: 1.4,
  inner: Ln(k.sideSeam(TANK, 26, P, 8) + k.sideSeam(TANK, 120, P, 8), { tone: "faint" }),
  extra:
    Ln(k.planOutline(k.insetPlan(TANK, 10), TANK_H, P, 8), { tone: "lo" }) +
    S(k.cylinder(TANK.x + 50, TANK.y + 48, 22, TANK_H, 5, P, 48, 0.6), { tone: "mid" }) +
    Ln(k.knurl(TANK.x + 50, TANK.y + 48, 22, TANK_H + 0.6, TANK_H + 5, 64, P, 0.3), { tone: "lo" }) +
    Ln(k.ring(TANK.x + 50, TANK.y + 48, 16, TANK_H + 5, P, 48), { tone: "faint" }) +
    Dots([at([TANK.x + 50, TANK.y + 48, TANK_H + 5])], { size: 0.6, tone: "hi" }),
  ground: true,
});
// Visor de nivel: tubo transparente con escala 0–3000 L sobre la cara delantera.
const GAUGE_X = TANK.x + 22;
const LEVEL_Z = [22, 178];
const levelZ = (litres) => LEVEL_Z[0] + ((LEVEL_Z[1] - LEVEL_Z[0]) * litres) / M.TANK.capacity;
{
  const face = TANK_FRONT;
  const strip = [GAUGE_X + 8, face, LEVEL_Z[0] - 6];
  const marks = [];
  const majors = [];
  for (let l = 0; l <= M.TANK.capacity; l += 100) {
    const z = levelZ(l);
    const len = l % 500 === 0 ? 7 : 4;
    (l % 500 === 0 ? majors : marks).push(k.segment([strip[0], face, z], [strip[0] + len, face, z], P));
  }
  const rail = k.slabOf({ x: GAUGE_X + 8, y: face, w: 10, d: 1.2, r: 0.3 }, LEVEL_Z[0] - 6, LEVEL_Z[1] - LEVEL_Z[0] + 12, P, 2);
  put("tank.scale", [GAUGE_X + 12, face + 1, 100], S(rail, { tone: "lo", crease: "none" }) + Ln(marks.join(""), { tone: "lo" }) + Ln(majors.join(""), { tone: "mid" }), [record(A.slab({ x: GAUGE_X + 8, y: face, w: 10, d: 1.2, r: 0.3 }, LEVEL_Z[0] - 6, LEVEL_Z[1] - LEVEL_Z[0] + 12, { name: "tank.scale" }, 2))], { bias: 0.5 });
  for (const z of [LEVEL_Z[0] - 10, LEVEL_Z[1] + 4]) box(`tank.boss${z}`, [GAUGE_X - 5, face, z], [GAUGE_X + 5, face + 6, z + 6], { tone: "mid" }, { r: 1.2, bias: 0.6 });
  post("tank.visor", GAUGE_X, face + 3, 3, LEVEL_Z[0] - 4, LEVEL_Z[1] + 4, { tone: "hi" }, { bias: 0.7, steps: 20, inner: Ln(k.sideArc(GAUGE_X, face + 3, 3, levelZ(M.TANK.litres), P, 16), { tone: "mid" }) });
}

// ── Filtro de aspiración en línea, sobre su soporte.
box("filter.stand", [FILTER[0] - 14, FILTER[1] - 14, 0], [FILTER[0] + 14, FILTER[1] + 14, 8], { tone: "lo" }, { r: 2, steps: 4, bevel: 0.6, extra: Dots(k.corners({ x: FILTER[0] - 14, y: FILTER[1] - 14, w: 28, d: 28 }, 4).map(([x, y]) => at([x, y, 8])), { size: 0.5 }) });
post("filter.post", FILTER[0], FILTER[1], 5, 8, 36, { tone: "mid" }, { steps: 24 });
post("filter.bowl", FILTER[0], FILTER[1], 16, 36, 88, { tone: "hi" }, { steps: 48, inner: Ln(k.sideArc(FILTER[0], FILTER[1], 16, 44, P, 32) + k.sideArc(FILTER[0], FILTER[1], 16, 80, P, 32), { tone: "faint" }) });
box("filter.head", [FILTER[0] - 19, FILTER[1] - 19, 88], [FILTER[0] + 19, FILTER[1] + 19, 110], { tone: "mid" }, { r: 3, steps: 4, bevel: 0.6, extra: Dots(k.corners({ x: FILTER[0] - 19, y: FILTER[1] - 19, w: 38, d: 38 }, 4).map(([x, y]) => at([x, y, 110])), { size: 0.5 }) });
post("filter.cap", FILTER[0], FILTER[1], 9, 110, 116, { tone: "mid" }, { steps: 28, extra: Ln(k.knurl(FILTER[0], FILTER[1], 9, 110.4, 116, 28, P, 0.3), { tone: "lo" }) });

// ── Soporte de la bomba: dos patas atornilladas al banco.
for (const [x0, x1] of [[-70, -36], [36, 70]]) {
  box(`pump.foot${x0}`, [x0, -66, 0], [x1, -4, 6], { tone: "lo" }, { r: 2, bevel: 0.6, extra: Dots([at([x0 + 6, -10, 6]), at([x1 - 6, -10, 6]), at([x0 + 6, -60, 6]), at([x1 - 6, -60, 6])], { size: 0.55 }) });
  box(`pump.leg${x0}`, [x0 + 6, -60, 6], [x1 - 6, -10, 38], { tone: "mid" }, { r: 1.5, bevel: 0.5 });
}

// ── Carcasa de la bomba (mitad trasera, cortada en y = 0). Cajas en x–z, fondo BACK.
const RECTS = {
  crank: [-62, 38, 62, 150],
  neck: [-40, 150, 40, 170],
  flange: [-66, 170, 66, 183],
  head: [-84, 185, 84, 262],
  inlet: [-118, 178, -84, 222],
  outlet: [84, 196, 128, 250],
};
for (const [name, [x0, z0, x1, z1]] of Object.entries(RECTS)) {
  box(`pump.${name}`, [x0, BACK, z0], [x1, 0, z1], { tone: "mid" }, { r: 0.6, steps: 2, bevel: 0.5, bias: -0.2 });
}
box("pump.rubber", [-66, BACK + 2, 183], [66, -0.2, 185], { tone: "lo" }, { r: 0.4, steps: 2, bevel: 0 });
// Tornillos pasantes de la culata y del cárter, tapón de aceite y mirilla.
for (const [x, y] of [[-78, -50], [78, -50], [-78, -17], [78, -17]]) post(`pump.bolt${x}${y}`, x, y, 4, 262, 268, { tone: "mid" }, { steps: 6, bevel: 0.3, extra: Ln(k.ring(x, y, 2.2, 268, P, 12), { tone: "faint" }) });
post("pump.oilcap", 52, -30, 8, 150, 158, { tone: "mid" }, { steps: 28, extra: Ln(k.knurl(52, -30, 8, 150.4, 158, 24, P, 0.3), { tone: "lo" }) + Dots([at([52, -30, 158])], { size: 0.5, tone: "hi" }) });
{
  const F = G.frameOf([62, -30, 70], [1, 0, 0], [0, 1, 0], [0, 0, 1]);
  turned("pump.sight", F, [[0, 10], [2.4, 10], [2.4, 7.4], [3.4, 7.4]], { tone: "mid" }, { extra: S(G.disc(3.3, 3.5, 5.6, F, P), { tone: "hi", lit: false }) + Ln(G.arcOf(F, 3.5, 5.6, P, { least: -1 }), { tone: "lo" }) });
}

// Tapas de válvula, toma del calderín y calderín: medios sólidos de revolución.
const CAPS = [-64, 64];
for (const x of CAPS) {
  const paths = halfRev(x, [[262, 11], [266, 11], [266, 10], [274, 10], [274, 0]], 24);
  put(`cap${x}`, [x, -5, 268], halfRevSvg(paths, { tone: "mid" }), [record(A.cylinder(x, 0, 11, 262, 12, { name: `cap${x}`, cut: FRONT }))], { bias: 0.1 });
}
const CHAMBER = { x: 112, z: 292, r: 34, wall: 4 };
{
  const prof = [[250, 7], [258, 7]];
  const zb = CHAMBER.z - CHAMBER.r;
  for (let i = 0; i <= 10; i++) {
    const a = (Math.PI / 2) * (1 - i / 10);
    const z = CHAMBER.z - CHAMBER.r * Math.sin(a);
    const r = CHAMBER.r * Math.cos(a);
    if (z >= 258) prof.push([z, Math.max(7, r), 1]);
  }
  prof[2] = [258, Math.sqrt(CHAMBER.r ** 2 - (CHAMBER.z - 258) ** 2)];
  prof.push([CHAMBER.z - 3, CHAMBER.r], [CHAMBER.z - 3, CHAMBER.r + 5], [CHAMBER.z + 3, CHAMBER.r + 5], [CHAMBER.z + 3, CHAMBER.r]);
  for (let i = 1; i <= 10; i++) {
    const a = (Math.PI / 2) * (i / 10);
    const z = CHAMBER.z + 3 + (CHAMBER.r - 3) * Math.sin(a);
    const r = CHAMBER.r * Math.cos(a);
    if (r > 5) prof.push([z, r, 1]);
  }
  const zt = CHAMBER.z + 3 + (CHAMBER.r - 3) * Math.sin(Math.acos(5 / CHAMBER.r));
  prof.push([zt, 5], [zt + 8, 5], [zt + 8, 3.4], [zt + 12, 3.4], [zt + 12, 0]);
  void zb;
  const paths = halfRev(CHAMBER.x, prof, 48);
  put("chamber", [CHAMBER.x, -12, CHAMBER.z], halfRevSvg(paths, { tone: "mid" }), [
    record(A.ball([CHAMBER.x, 0, CHAMBER.z], CHAMBER.r, { name: "chamber", owner: "chamber", cut: FRONT })),
    record(A.cylinder(CHAMBER.x, 0, CHAMBER.r + 5, CHAMBER.z - 3, 6, { name: "chamber.flange", owner: "chamber", cut: FRONT })),
    record(A.cylinder(CHAMBER.x, 0, 7, 250, 10, { name: "chamber.neck", owner: "chamber", cut: FRONT })),
  ], { bias: 0.1 });
  CHAMBER.top = zt;
}

// ── Grupo de mando: regulador de presión sobre su columna, manómetro, salida a boquillas y retorno.
const REG_C = [REG.x + 16, REG.y + REG.d / 2];
box("reg.base", [REG.x + 2, REG.y + 2, 0], [REG.x + 42, REG.y + REG.d - 2, 7], { tone: "lo" }, { r: 2, bevel: 0.6, extra: Dots(k.corners({ x: REG.x + 2, y: REG.y + 2, w: 40, d: REG.d - 4 }, 4).map(([x, y]) => at([x, y, 7])), { size: 0.5 }) });
post("reg.column", REG.x + 22, REG.y + REG.d / 2, 7, 7, REG_Z[0], { tone: "mid" }, { steps: 28, inner: Ln(k.sideArc(REG.x + 22, REG.y + REG.d / 2, 7, 60, P, 20) + k.sideArc(REG.x + 22, REG.y + REG.d / 2, 7, 140, P, 20), { tone: "faint" }) });
box("reg.body", [REG.x, REG.y, REG_Z[0]], [REG.x + REG.w, REG.y + REG.d, REG_Z[1]], { tone: "hi" }, {
  r: 2,
  bevel: 0.8,
  inner: Ln(k.sideSeam(REG, REG_Z[0] + 8, P, 3), { tone: "faint" }),
  extra: Dots([at([REG.x + 5, REG.y + 5, REG_Z[1]]), at([REG.x + REG.w - 5, REG.y + 5, REG_Z[1]]), at([REG.x + 5, REG.y + REG.d - 5, REG_Z[1]]), at([REG.x + REG.w - 5, REG.y + REG.d - 5, REG_Z[1]])], { size: 0.5 }),
});
post("reg.dome", REG_C[0], REG_C[1], 11, REG_Z[1], REG_Z[1] + 26, { tone: "mid" }, { steps: 40, inner: Ln(k.sideArc(REG_C[0], REG_C[1], 11, REG_Z[1] + 6, P, 28), { tone: "faint" }) });
post("reg.stem", REG_C[0], REG_C[1], 3, REG_Z[1] + 26, REG_Z[1] + 32, { tone: "mid" }, { steps: 16 });
const WHEEL = { r: 15, z: REG_Z[1] + 32, h: 7 };
post("reg.wheel", REG_C[0], REG_C[1], WHEEL.r, WHEEL.z, WHEEL.z + WHEEL.h, { tone: "hi" }, { steps: 56, bevel: 0.5, extra: Ln(k.knurl(REG_C[0], REG_C[1], WHEEL.r, WHEEL.z + 0.4, WHEEL.z + WHEEL.h, 60, P, 0.3), { tone: "lo" }) + Ln(k.ring(REG_C[0], REG_C[1], 4, WHEEL.z + WHEEL.h, P, 20), { tone: "faint" }), bias: 0.1 });
const DIAL = { x: REG.x + 44, y: REG.y + REG.d / 2, r: 15, z: REG_Z[1] + 12, h: 8, full: 25 };
post("reg.gaugestem", DIAL.x, DIAL.y, 3, REG_Z[1], DIAL.z, { tone: "mid" }, { steps: 6 });
{
  const top = DIAL.z + DIAL.h;
  const ticks = k.radialTicks(DIAL.x, DIAL.y, DIAL.r - 2.6, 25, 5, top, [1.3, 2.8], P, -0.75 * Math.PI, 1.5 * Math.PI);
  post("reg.gauge", DIAL.x, DIAL.y, DIAL.r, DIAL.z, top, { tone: "hi", crease: "lo" }, {
    steps: 56,
    bevel: 0.6,
    bias: 0.2,
    extra:
      Ln(k.knurl(DIAL.x, DIAL.y, DIAL.r, top - 2.6, top, 70, P, 0.3), { tone: "lo" }) +
      Ln(k.ring(DIAL.x, DIAL.y, DIAL.r - 1.8, top, P, 56), { tone: "lo" }) +
      Ln(ticks.minor, { tone: "lo" }) +
      Ln(ticks.major, { tone: "mid" }) +
      k.faceTextSvg(k.topMatrix([DIAL.x - 3.6, DIAL.y + 7], top, P, "x"), "bar", { size: 2.6, tone: "lo" }),
  });
  DIAL.top = top;
}

// ── Barra del arco de boquillas.
box("bar.foot", [BAR[0] - 12, BAR[1] - 12, 0], [BAR[0] + 12, BAR[1] + 12, 6], { tone: "lo" }, { r: 2, bevel: 0.6, extra: Dots(k.corners({ x: BAR[0] - 12, y: BAR[1] - 12, w: 24, d: 24 }, 4).map(([x, y]) => at([x, y, 6])), { size: 0.5 }) });
post("bar.post", BAR[0], BAR[1], 4.5, 6, 232, { tone: "mid" }, { steps: 24, extra: Dots([at([BAR[0], BAR[1], 232])], { size: 0.6 }) });

// ── Tuberías: tramos rectos entre codos y tes de bola, con bridas en las bocas.
const PIPES = [];
function pipe(name, points, r, { tone = "mid", flanges = [true, true], maxLength = 26, bias = 0, touch } = {}) {
  const route = points.length > 2 ? G.fillet(points, Math.max(6, r * 2.6), 9, { P, tube: r }) : points;
  const total = G.pathLength(route);
  const FL = 3.2;
  const gaps = [...(flanges[0] ? [[0, FL]] : []), ...(flanges[1] ? [[total - FL, total]] : [])];
  R.route(name, route, r, { gaps, piece: "rig", rims: flanges.map((on) => (on ? r * 1.7 : r)), ...(touch ? { touch } : {}) });
  PIPES.push({ name, route, r });
  tubePieces(route, r, P, { maxLength, spacing: 1.4, gaps }).forEach((piece, index) =>
    put(`${name}:${index}`, piece.mid, tubeSvg(piece, { tone }), [A.tube(piece.points, piece.radii)], { bias, route: name, chunk: index }),
  );
  [0, 1].forEach((index) => {
    if (!flanges[index]) return;
    const end = index ? route[route.length - 1] : route[0];
    const tangent = G.tangentAlong(route, index ? total : 0);
    const F = G.frameAlong(end, tangent);
    const [s0, s1] = index ? [-FL, 0] : [0, FL];
    const shape = record(A.disc(F, s0, s1, r * 1.7, { name: `${name}.fl${index}`, owner: name }));
    const bolts = Math.abs(G.dot3(tangent, V)) > 0.12 ? Dots(G.dotsOf(F, index ? s0 : s1, r * 1.35, 6, P, { all: true }), { size: 0.45 }) : "";
    put(`${name}.fl${index}`, G.add3(end, G.mul3(tangent, (s0 + s1) / 2)), S(G.disc(s0, s1, r * 1.7, F, P, { bevel: 0.3 }), { tone: "mid" }) + bolts, [shape], { bias: bias + 0.1 });
  });
  return route;
}
// Línea rígida: un codo o una te de bola en cada vértice, el tubo apoya su canto en la bola.
function rigid(name, points, r, { flanges = [true, true], touch, tone = "mid" } = {}) {
  const rb = r * 1.6;
  const trim = Math.sqrt(rb * rb - r * r);
  const balls = [];
  for (let i = 1; i < points.length - 1; i++) {
    const c = points[i];
    const shape = record(A.ball(c, rb, { name: `${name}.ball${i}` }));
    balls.push(shape);
    put(`${name}.ball${i}`, c, S(G.sphereOf(c, rb, P, { steps: 32 }), { tone: "mid" }), [shape], { bias: 0.05 });
  }
  for (let i = 0; i + 1 < points.length; i++) {
    const a = points[i];
    const b = points[i + 1];
    const dir = G.unit3(G.sub3(b, a));
    const from = i > 0 ? G.add3(a, G.mul3(dir, trim)) : a;
    const to = i + 2 < points.length ? G.sub3(b, G.mul3(dir, trim)) : b;
    pipe(`${name}.${i}`, [from, to], r, { tone, flanges: [i === 0 && flanges[0], i + 2 === points.length && flanges[1]], touch });
  }
  return { rb, trim, balls };
}
// Aspiración: fondo de la cuba → filtro → boca de aspiración, por debajo.
rigid("suction.a", [[TANK.x + TANK.w, PY, 24], [-158, PY, 24], [-158, PY, 99], [FILTER[0] - 19, PY, 99]], 5.5);
pipe("suction.b", [[FILTER[0], PY, 116], [FILTER[0], PY, RECTS.inlet[1]]], 5.5, { flanges: [false, true] });
// Impulsión: colector de salida → regulador.
pipe("delivery", [[RECTS.outlet[2], PY, 226], [REG.x, PY, 226]], 5);
// Retorno a la cuba: cara trasera del regulador → por detrás de la bomba → tapa de la cuba.
rigid("return", [[REG.x + 40, REG.y, 222], [REG.x + 40, -104, 222], [TANK.x + 40, -104, 222], [TANK.x + 40, -104, TANK_H]], 4.5);
// Salida a boquillas: cara delantera del regulador → codo → tubo vertical con una te por boquilla.
const TEE = rigid("nozzles", [[LINE_X, REG.y + REG.d, 222], [LINE_X, BAR[1], 222], ...NOZZLE_Z.map((z) => [LINE_X, BAR[1], z]), [LINE_X, BAR[1], 128]], 4, { flanges: [true, false], touch: ["bracket*"] });
{
  const F = G.frameOf([LINE_X, BAR[1], 128], [0, 0, -1], [1, 0, 0], [0, 1, 0]);
  turned("nozzles.plug", F, [[0, 5.4], [5, 5.4], [5, 3.4], [7, 3.4]], { tone: "mid" });
}
// Portaboquillas con antigoteo y boquilla de cono hueco, cada uno en su te.
const NECK = Math.sqrt((TEE.rb) ** 2 - 3 ** 2);
const NOZZLES = NOZZLE_Z.map((z) => ({ z, tip: [LINE_X + NECK + 22.6, BAR[1], z] }));
for (const [index, { z }] of NOZZLES.entries()) {
  const F = G.frameOf([LINE_X + NECK, BAR[1], z], [1, 0, 0], [0, 1, 0], [0, 0, 1]);
  turned(`nozzle${z}.body`, F, [[0, 3], [4, 3], [4, 5], [11, 5], [11, 6.4], [17.6, 6.4]], { tone: "mid" }, { bias: 0.15, meta: { seated: true, cut: TEE.balls[index + 1] } });
  turned(`nozzle${z}.cap`, F, [[17.6, 7.2], [21.6, 7.2], [21.6, 3.2, 1], [22.4, 1.6]], { tone: "hi" }, { bias: 0.2, extra: Dots([at([LINE_X + NECK + 22.6, BAR[1], z])], { size: 0.6, tone: "hi" }) });
}
// Pletinas que sujetan el tubo a la barra: tocan los dos de canto.
for (const z of [210, 174, 138]) box(`bracket${z}`, [BAR[0] + 4.5, BAR[1] - 3, z - 3], [LINE_X - 4, BAR[1] + 3, z + 3], { tone: "lo" }, { r: 0.6, steps: 2, bevel: 0.3, bias: 0.05 });

// ── Sección (plano y = 0): polígonos en (x, z) → coordenadas de cara (x, −z).
const rectPoly = ([x0, z0, x1, z1], r = 0, steps = 4) => (r ? k.roundedPlan({ x: x0, y: z0, w: x1 - x0, d: z1 - z0, r }, steps) : [[x0, z0], [x1, z0], [x1, z1], [x0, z1]]);
const circlePoly = (cx, cz, r, n = 48) => Array.from({ length: n }, (_, i) => [cx + r * Math.cos((i / n) * Math.PI * 2), cz + r * Math.sin((i / n) * Math.PI * 2)]);
const asPoly = (ring) => [[...ring, ring[0]]];
const union = (...polys) => polygonClipping.union(...polys.map(asPoly));
const flip = (multi) => multi.map((poly) => poly.map((ring) => ring.map(([x, z]) => [x, -z])));
const dOf = (multi) => flip(multi).map((poly) => poly.map((ring) => `M${ring.map(([x, y]) => `${f2(x)} ${f2(y)}`).join("L")}Z`).join("")).join("");
const dRing = (ring) => `M${ring.map(([x, z]) => `${f2(x)} ${f2(-z)}`).join("L")}Z`;

const SOLID = union(
  rectPoly(RECTS.crank),
  rectPoly(RECTS.neck),
  rectPoly(RECTS.flange),
  rectPoly(RECTS.head),
  rectPoly(RECTS.inlet),
  rectPoly(RECTS.outlet),
  rectPoly([106, 249, 118, 259]),
  circlePoly(CHAMBER.x, CHAMBER.z, CHAMBER.r, 72),
  rectPoly([CHAMBER.x - CHAMBER.r - 5, CHAMBER.z - 3, CHAMBER.x + CHAMBER.r + 5, CHAMBER.z + 3]),
  rectPoly([CHAMBER.x - 5, CHAMBER.top - 1, CHAMBER.x + 5, CHAMBER.top + 8]),
  rectPoly([CHAMBER.x - 3.4, CHAMBER.top + 7, CHAMBER.x + 3.4, CHAMBER.top + 12]),
);
// Huecos: cárter (aceite), cilindro, platillo de membrana, cámara, válvulas, canales, calderín.
const dish = [[-27, 172], [27, 172], [50, 182], [50, 184.5], [-50, 184.5], [-50, 182]];
const domeTop = (x) => 185 + 25 * Math.sqrt(Math.max(0, 1 - (x / 50) ** 2));
const dome = [[-50, 184], [50, 184], ...Array.from({ length: 33 }, (_, i) => { const x = 50 - (i / 32) * 100; return [x, domeTop(x)]; })];
const SUCTION_X = -64;
const DELIVERY_X = 64;
const CAV = {
  crank: union(rectPoly([-46, 52, 46, 142], 14, 6), rectPoly([-27, 140, 27, 173])),
  water: union(
    dish,
    dome,
    rectPoly([SUCTION_X - 12, 190, SUCTION_X + 12, 207], 3),
    rectPoly([SUCTION_X - 5, 205, SUCTION_X + 5, 212]),
    rectPoly([SUCTION_X - 14, 210, SUCTION_X + 14, 240], 4),
    [[SUCTION_X + 12, 214], [SUCTION_X + 14, 226], [-36, 204], [-44, 196]],
    rectPoly([-109, 194, SUCTION_X - 8, 205], 2),
    rectPoly([-109, 172, -93, 205], 3),
    rectPoly([DELIVERY_X - 12, 190, DELIVERY_X + 12, 207], 3),
    [[44, 196], [50, 186], [DELIVERY_X - 10, 192], [DELIVERY_X - 10, 204]],
    rectPoly([DELIVERY_X - 5, 205, DELIVERY_X + 5, 212]),
    rectPoly([DELIVERY_X - 14, 210, DELIVERY_X + 14, 240], 4),
    rectPoly([DELIVERY_X + 8, 220, 134, 232], 2),
    rectPoly([CHAMBER.x - 4, 220, CHAMBER.x + 4, 262]),
  ),
  chamber: union(circlePoly(CHAMBER.x, CHAMBER.z, CHAMBER.r - CHAMBER.wall, 72)),
  air: union(rectPoly([CHAMBER.x - 1.2, CHAMBER.top - 2, CHAMBER.x + 1.2, CHAMBER.top + 14])),
};
const CUT_FACE = polygonClipping.difference(SOLID, CAV.crank, CAV.water, CAV.chamber, CAV.air);
const clipTo = (multi) => polygonClipping.intersection(multi, SOLID);

// Piezas fijas cortadas: asientos de válvula, tapones, tornillo del calderín.
const seat = (x) => polygonClipping.difference(asPoly(rectPoly([x - 14, 206, x + 14, 210.5])), asPoly(rectPoly([x - 6, 205, x + 6, 212])));
const capInsert = (x) => union(rectPoly([x - 10, 236, x + 10, 262]), rectPoly([x - 11, 262, x + 11, 274]));
const STATIC_PARTS = [
  { d: dOf(seat(SUCTION_X)), cls: "bm-insert" },
  { d: dOf(seat(DELIVERY_X)), cls: "bm-insert" },
  { d: dOf(capInsert(SUCTION_X)), cls: "bm-insert" },
  { d: dOf(capInsert(DELIVERY_X)), cls: "bm-insert" },
];

// Seams entre piezas de la carcasa (juntas en el corte).
const seams = [
  [[-66, 183], [66, 183]],
  [[-84, 185], [-66, 185]],
  [[66, 185], [84, 185]],
  [[-84, 205], [-84, 222]],
  [[84, 196], [84, 220]],
  [[84, 232], [84, 250]],
  [[-40, 170], [-40, 172]],
  [[40, 170], [40, 172]],
];
const seamD = seams.map(([a, b]) => `M${a[0]} ${-a[1]}L${b[0]} ${-b[1]}`).join("");

const MATRIX = k.sideMatrix([0, 0], 0, P, "left");
const sectionSvg = [
  `<g class="bm-section" transform="${MATRIX}">`,
  `<path class="bm-void" d="${dOf(clipTo(CAV.crank))}"/>`,
  `<path class="bm-oil" data-part="oil" d="${dOf(clipTo(union(rectPoly([-46, 52, 46, 66], 14, 6))))}"/>`,
  `<path class="bm-water" d="${dOf(clipTo(CAV.water))}"/>`,
  `<path class="bm-air" d="${dOf(CAV.chamber)}"/>`,
  `<path class="bm-water" data-part="chamber-water" d=""/>`,
  `<g data-part="flow-in" class="bm-flow"></g><g data-part="flow-out" class="bm-flow"></g>`,
  `<g data-part="crank"></g>`,
  `<path class="bm-part" data-part="rod" d=""/>`,
  `<g data-part="piston"></g>`,
  `<path class="bm-cut" fill-rule="evenodd" d="${dOf(CUT_FACE)}"/>`,
  ...STATIC_PARTS.map((p) => `<path class="${p.cls}" fill-rule="evenodd" d="${p.d}"/>`),
  `<path class="iso-line" data-tone="lo" d="${seamD}"/>`,
  `<g data-part="valves"></g>`,
  `<path class="bm-rubber" data-part="membrane" d=""/>`,
  `<path class="bm-rubber" data-part="chamber-membrane" d=""/>`,
  `</g>`,
].join("");
// La sección va como un objeto más, por delante de toda la carcasa; se registra como lámina delgada.
put("section", [0, 0.5, 180], sectionSvg, [
  record(A.slab({ x: -118, y: 0, w: 270, d: 0.4, r: 0 }, 38, 300, { name: "section.face" }, 1)),
], { bias: 2 });

// ── Pulverización: grupo vacío que rellena el código vivo.
const SPRAY_SVG = `<g data-part="spray" class="bm-spray"></g>`;

A.settle(R, P);
R.items.sort((a, b) => a.key - b.key);
const groups = R.items.map((item) => `<g class="it" data-name="${item.name}">${item.svg}</g>`);

// Aguja del manómetro, puntero del volante y flotador: piezas que el código vivo reescribe.
const needleAt = (bar) => {
  const t = -0.75 * Math.PI + (Math.min(DIAL.full, Math.max(0, bar)) / DIAL.full) * 1.5 * Math.PI;
  const [x, y] = [DIAL.x, DIAL.y];
  return k.lineOnTop([x - Math.sin(t) * 3, y + Math.cos(t) * 3], [x + Math.sin(t) * (DIAL.r - 4), y - Math.cos(t) * (DIAL.r - 4)], DIAL.top + 0.2, P);
};
const wheelAt = (bar) => {
  const t = (bar / 25) * Math.PI * 2 * 1.5;
  return k.lineOnTop([REG_C[0] + Math.sin(t) * 5, REG_C[1] - Math.cos(t) * 5], [REG_C[0] + Math.sin(t) * (WHEEL.r - 2), REG_C[1] - Math.cos(t) * (WHEEL.r - 2)], WHEEL.z + WHEEL.h, P);
};

const LABEL_TEXT =
  "Bomba de pistón-membrana de una atomizadora, cortada por la mitad sobre un banco. A la izquierda, la cuba de 3000 litros con su visor de nivel y el filtro de aspiración. En el centro, el cárter con el cigüeñal, la biela y el pistón; encima, la membrana de goma, la culata con la válvula de aspiración y la de impulsión, y el calderín de aire. A la derecha, el regulador de presión con su manómetro, el retorno a la cuba y el arco con dos boquillas pulverizando.";
const svg = k.figureSvg({
  width: W,
  height: H,
  label: LABEL_TEXT,
  body: [...back, `<g class="bm-rig">${groups.join("")}</g>`, `<path class="iso-line" data-tone="lit" data-free="start" data-part="needle" d="${needleAt(15)}" style="stroke-width:1.1"/>`, `<path class="iso-line" data-tone="hi" data-free="end" data-part="wheel" d="${wheelAt(15)}"/>`, SPRAY_SVG],
});

const DATA = {
  P,
  matrix: MATRIX,
  shaft: SHAFT,
  pump: M.PUMP,
  nozzlesModel: M.NOZZLES,
  tank: M.TANK,
  charge: M.CHARGE,
  suctionX: SUCTION_X,
  deliveryX: DELIVERY_X,
  chamber: CHAMBER,
  dial: DIAL,
  regC: REG_C,
  wheel: WHEEL,
  tips: NOZZLES.map((n) => n.tip),
  flowIn: [[-101, 176], [-101, 199], [SUCTION_X, 199], [SUCTION_X, 222], [-40, 202], [-20, 194]],
  flowOut: [[20, 194], [46, 192], [DELIVERY_X, 198], [DELIVERY_X, 222], [100, 226], [132, 226]],
};

const MODEL = readFileSync(join(HERE, "model.mjs"), "utf8").replace(/^export /gm, "");
const LIVE = readFileSync(join(HERE, "live.js"), "utf8").replace("__DATA__", JSON.stringify(DATA)).replace("__NEEDLE__", needleAt.toString().replace(/k\.lineOnTop/g, "lineOnTop")).replace("__WHEEL__", wheelAt.toString().replace(/k\.lineOnTop/g, "lineOnTop"));
const CSS = readFileSync(join(HERE, "bomba.css"), "utf8");

const body =
  `<style>${CSS}</style>` +
  k.plateHtml({
    fig: "Fig 1",
    title: "Bomba de atomizadora",
    hint: "← → régimen · ↑ ↓ presión · espacio boquillas",
    readout: "540 rpm · 144 L/min · 15 bar",
    keys: [
      { mark: "lit", label: "Membrana y pistón: lo que bombea" },
      { mark: "raised", label: "Válvulas de aspiración (izquierda) e impulsión (derecha)" },
      { mark: "flat", label: "Corte de la carcasa; agua, aceite y aire en sus huecos" },
    ],
    caption:
      "Una bomba de pistón-membrana como las de las atomizadoras con cuba de 3000 L, cortada por una de sus cuatro membranas. La toma de fuerza del tractor gira el cigüeñal a 540 rpm; la biela sube y baja el pistón 28 mm, y el pistón arrastra la membrana de goma, que separa el aceite del cárter del agua de la cámara. Al bajar, la cámara crece, la válvula de aspiración se abre y entra agua de la cuba a través del filtro; al subir, esa válvula se cierra, se abre la de impulsión y el agua sale al calderín y al regulador. El calderín, con aire precargado a 4,5 bar, suaviza los golpes de cada membrana. Cuatro membranas de 70 cm³ dan unos 144 L/min; el regulador mantiene la presión tarada y devuelve a la cuba lo que no gastan las 16 boquillas (1,5 L/min a 10 bar cada una), y ese retorno es el que agita el caldo. Mueve el puntero a lo ancho para cambiar el régimen y a lo alto para tarar la presión.",
    body: `<div class="iso-stage bm-stage" tabindex="0" role="group" aria-label="Bomba de atomizadora. Flechas izquierda y derecha: régimen de la toma de fuerza. Flechas arriba y abajo: presión del regulador. Espacio: abrir o cerrar las boquillas.">${svg}<span class="bm-sr" role="status" aria-live="polite" data-status></span></div>`,
  });

mkdirSync(OUT, { recursive: true });
const name = THEME === "light" ? "bomba-atomizadora-light.html" : "bomba-atomizadora.html";
const page = k.pageHtml({ title: "Bomba de atomizadora", theme: THEME, body, script: kitScript() + MODEL + LIVE, width: 760 });
writeFileSync(join(OUT, name), page.replace("<html lang=\"en\">", "<html lang=\"es\">"));
console.log("wrote", name, `${(page.length / 1024).toFixed(0)} KB`, `${R.items.length} items`, `scale ${KS.toFixed(3)}`);

A.auditOrExit(R, P, {});
