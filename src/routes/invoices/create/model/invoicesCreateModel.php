<?php


namespace Invoices;

use PDO;
use PDOException;

class invoicesCreateModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_prices(): array
    {
        try {
            $req = $this->pdo->prepare('SELECT category, casco, lesson, exam, oacp, theorical_lesson FROM ges_storagehost_ch.prices');
            $req->execute();
            return $req->fetchAll();
        } catch (PDOException $e) {
            return array();
        }
    }

    public function create_invoice(array $data): array
    {
        try {
            $req = $this->pdo->prepare('INSERT INTO ges_storagehost_ch.invoices(student_id, category, casco_nbr, lesson_nbr, exam_nbr, oacp_nbr, theorical_lesson_nbr, casco_price, lesson_price, exam_price, oacp_price, theorical_lesson_price, casco_total, lesson_total, exam_total, oacp_total, theorical_lesson_total, paid, paid_by, date, total) VALUES (:student_id, :category, :casco_nbr, :lesson_nbr, :exam_nbr, :oacp_nbr, :theorical_lesson_nbr, :casco_price, :lesson_price, :exam_price, :oacp_price, :theorical_lesson_price, :casco_total, :lesson_total, :exam_total, :oacp_total, :theorical_lesson_total, :paid, :paid_by, :date, :total)');
            $req->execute(array(
                    ':student_id' => $data['student_id'],
                    ':category' => $data['category'],
                    ':casco_nbr' => $data['casco_nbr'],
                    ':lesson_nbr' => $data['lesson_nbr'],
                    ':exam_nbr' => $data['exam_nbr'],
                    ':oacp_nbr' => $data['oacp_nbr'],
                    ':theorical_lesson_nbr' => $data['theorical_lesson_nbr'],
                    ':casco_price' => $data['casco_price'],
                    ':lesson_price' => $data['lesson_price'],
                    ':exam_price' => $data['exam_price'],
                    ':oacp_price' => $data['oacp_price'],
                    ':theorical_lesson_price' => $data['theorical_lesson_price'],
                    ':casco_total' => $data['casco_total'],
                    ':lesson_total' => $data['lesson_total'],
                    ':exam_total' => $data['exam_total'],
                    ':oacp_total' => $data['oacp_total'],
                    ':theorical_lesson_total' => $data['theorical_lesson_total'],
                    ':paid' => 0,
                    ':paid_by' => null,
                    ':date' => date("Y-m-d H:i:s"),
                    ':total' => $data['total']
                )
            );

            return array(
                'status' => 'success',
                'data' => array(
                    'id' => $this->pdo->lastInsertId(),
                    'category' => $data['category'],
                    'casco_total' => $data['casco_total'],
                    'lesson_total' => $data['lesson_total'],
                    'exam_total' => $data['exam_total'],
                    'oacp_total' => $data['oacp_total'],
                    'theorical_lesson_total' => $data['theorical_lesson_total'],
                    'paid' => 0,
                    'paid_by' => null,
                    'date' => date("Y-m-d H:i:s"),
                    'total' => $data['total']
                ),
                'date' => time()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'date' => time()
            );
        }
    }
}