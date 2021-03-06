<?php

namespace Invoices;

use PDO;
use PDOException;

class invoicesDisplayModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_invoices()
    {
        try {
            $req = $this->pdo->prepare('SELECT invoice_id, student_id, paid, date, total FROM ges_storagehost_ch.invoices');
            $req->execute();

            if ($req->rowCount() > 0) {
                return array(
                    'status' => 'success',
                    'data' => $req->fetchAll(),
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'no_invoices',
                    'date' => time()
                );
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }

    public function get_invoice_by_id(int $id)
    {
        try {
            $req = $this->pdo->prepare('SELECT invoices.invoice_id, student_id, invoices.category, casco_nbr, lesson_nbr, exam_nbr, oacp_nbr, theorical_lesson_nbr, casco_price, lesson_price, exam_price, oacp_price, theorical_lesson_price, casco_total, lesson_total, exam_total, oacp_total, theorical_lesson_total, paid, paid_by, date, total, last_name, first_name, address, postal_code, city, phone FROM ges_storagehost_ch.invoices INNER JOIN students s on invoices.student_id = s.id WHERE ges_storagehost_ch.invoices.invoice_id = :id');
            $req->bindParam(':id', $id);
            $req->execute();

            if ($req->rowCount() > 0) {
                return array(
                    'status' => 'success',
                    'data' => $req->fetch(),
                    'date' => time()
                );
            } else {
                return array(
                    'status' => 'error',
                    'message' => 'invoice_not_found',
                    'date' => time()
                );
            }
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}