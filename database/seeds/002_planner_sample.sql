-- =============================================================
--  Seeder de prueba para el módulo Planner.
--  Inserta un único objetivo activo. Sirve para que el primer
--  desarrollo de PlannerAIService tenga datos con los que trabajar.
--
--  Aplicar con:
--    mysql -u USUARIO -p martincarmona < database/seeds/002_planner_sample.sql
--  o pegando el contenido en phpMyAdmin → SQL.
-- =============================================================

INSERT INTO `planner_goals`
    (`title`, `description`, `horizon_weeks`, `priority`, `status`)
VALUES
    (
        'Lanzar martincarmona.com con portfolio completo',
        'Terminar la web pública: narrativa, case studies, maquetación y deploy final. Es el proyecto ancla del trimestre.',
        12,
        1,
        'active'
    );
