# Bomba de atomizadora (figura Anatomy)

Figura interactiva de la bomba de pistón-membrana de una atomizadora con cuba de 3000 L, hecha con el skill Anatomy (`.claude/skills/anatomy`).

- `model.mjs`: el modelo (cinemática biela-manivela, caudal, boquillas, regulador y calderín). Todos los números de la figura salen de aquí.
- `build.mjs`: genera la geometría y escribe la página en `public/anatomia/`.
- `live.js`: animación e interacción (se inserta en la página).
- `bomba.css`: tonos de la sección (corte, agua, aceite, aire y goma).

```sh
node resources/anatomy/bomba-atomizadora/build.mjs           # tema oscuro
node resources/anatomy/bomba-atomizadora/build.mjs --light   # tema claro
node resources/anatomy/bomba-atomizadora/build.mjs --audit   # comprueba la geometría (tuberías, apoyos, orden de pintado)
```

Controles: ← → régimen de la toma de fuerza, ↑ ↓ presión del regulador, espacio abre o cierra las boquillas. Con el puntero, de izquierda a derecha cambia el régimen y de arriba abajo la presión.
