<?php

/**
 * Model – Monthly Feedback
 *
 * Este archivo complementa el controlador con consultas de bajo nivel
 * y sirve también como referencia del esquema SQL necesario.
 *
 * =========================================================
 *  ESQUEMA SQL — ejecutar en u961992735_plataforma
 * =========================================================
 *
 * CREATE TABLE IF NOT EXISTS monthly_feedbacks (
 *     id             INT AUTO_INCREMENT PRIMARY KEY,
 *     client_id      INT NOT NULL,
 *     period_id      INT NOT NULL,
 *     token          VARCHAR(64) NOT NULL UNIQUE  COMMENT 'Token único para el link del cliente',
 *     status         ENUM('pendiente','completado','vencido') NOT NULL DEFAULT 'pendiente',
 *     created_by     INT NULL                     COMMENT 'ID del usuario que generó el link',
 *     created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 *     FOREIGN KEY (client_id) REFERENCES clients(id),
 *     FOREIGN KEY (period_id) REFERENCES periods(id)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 * CREATE TABLE IF NOT EXISTS monthly_feedback_responses (
 *     id                  INT AUTO_INCREMENT PRIMARY KEY,
 *     feedback_id         INT NOT NULL,
 *     overall_rating      TINYINT(1) NOT NULL          COMMENT 'Calificación general 1–5',
 *     lead_quality        ENUM('buena','regular','mala') NOT NULL COMMENT 'Calidad general de los leads',
 *     leads_contacted     INT NULL                     COMMENT 'Cantidad de leads contactados',
 *     leads_quality_good  INT NULL                     COMMENT 'Cantidad de leads de calidad',
 *     observations        TEXT NULL                    COMMENT 'Comentarios libres del cliente',
 *     submitted_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     FOREIGN KEY (feedback_id) REFERENCES monthly_feedbacks(id) ON DELETE CASCADE
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 *
 * =========================================================
 */

class MonthlyFeedbackModel
{
    // Reservado para consultas específicas que no encajen en el controlador.
    // Por ahora toda la lógica vive en MonthlyFeedback_Controller.
}
