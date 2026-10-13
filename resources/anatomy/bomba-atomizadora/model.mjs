// Modelo de la bomba de pistón-membrana de una atomizadora con cuba de 3000 L.
// Todo lo que la figura mueve o escribe sale de aquí: cinemática biela-manivela,
// caudal de la bomba, curva de las boquillas, regulador de presión y calderín.

export const PUMP = {
  membranes: 4, // la figura secciona una de las cuatro
  stroke: 28, // mm, dos veces la excentricidad
  e: 14, // mm, excentricidad de la muñequilla
  rod: 70, // mm, longitud de la biela entre centros
  disp: 70, // cm³ que desplaza una membrana por vuelta
  etaV: 0.95, // rendimiento volumétrico
  rpmMax: 540, // toma de fuerza normalizada
  pMax: 50, // bar, presión máxima de la bomba
};

export const NOZZLES = { count: 16, qRef: 1.5, pRef: 10 }; // 16 boquillas de cono hueco, 1,5 L/min a 10 bar
export const TANK = { capacity: 3000, litres: 2400 };
export const CHARGE = { bar: 4.5 }; // precarga de aire del calderín, un 30 % de 15 bar

// Caudal teórico de la bomba en L/min.
export const pumpFlow = (rpm) => (PUMP.membranes * PUMP.disp * PUMP.etaV * rpm) / 1000;
// Caudal de todas las boquillas a la presión p (q ∝ √p).
export const nozzleFlow = (p, open = true) => (open ? NOZZLES.count * NOZZLES.qRef * Math.sqrt(Math.max(0, p) / NOZZLES.pRef) : 0);
// Presión que alcanza el circuito: la tarada en el regulador si sobra caudal; si no, la que dan las boquillas con todo el caudal.
export function pressureOf(rpm, set, open = true) {
  const q = pumpFlow(rpm);
  if (!open) return q > 0.5 ? set : (set * q) / 0.5;
  const reach = NOZZLES.pRef * (q / (NOZZLES.count * NOZZLES.qRef)) ** 2;
  return Math.min(set, reach);
}
// Reparto del caudal: lo que va a las boquillas y lo que vuelve a la cuba por el retorno (agitación).
export function flowsOf(rpm, p, open = true) {
  const pump = pumpFlow(rpm);
  const nozzles = Math.min(pump, nozzleFlow(p, open));
  return { pump, nozzles, back: Math.max(0, pump - nozzles) };
}

// Cinemática de una membrana. θ = 0 en el punto muerto superior.
export function pistonAt(theta) {
  const { e, rod } = PUMP;
  const px = e * Math.sin(theta);
  const pz = e * Math.cos(theta);
  const wrist = pz + Math.sqrt(rod * rod - px * px);
  return { pin: [px, pz], wrist };
}
export const lift = (theta) => pistonAt(theta).wrist - pistonAt(0).wrist + PUMP.stroke / 2; // −14 … +14 mm respecto a la mitad
// Velocidad del pistón (mm/rad): positiva subiendo (impulsión), negativa bajando (aspiración).
export const speedAt = (theta) => (lift(theta + 1e-3) - lift(theta - 1e-3)) / 2e-3;

// Calderín: el aire precargado a CHARGE.bar se comprime (p·V constante); la fracción que llena el agua es 1 − p0/p.
export const chamberShare = (p) => (p > CHARGE.bar ? Math.min(0.85, 1 - CHARGE.bar / p) : 0);

export const autonomy = (litres, q) => (q > 0.01 ? litres / q : Infinity); // minutos
