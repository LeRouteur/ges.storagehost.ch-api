<?php

namespace Invoices;

use PDO;

require_once __DIR__ . "/model/invoicesCreateModel.php";

class invoicesCreate
{
    private PDO $pdo;
    private array $data;
    private array $valid_data;
    private array $db_price;

    public function __construct(PDO $pdo, array $data)
    {
        $this->pdo = $pdo;
        $this->data = $data;
        $this->get_prices();
    }

    private function get_prices()
    {
        $prices = (new invoicesCreateModel($this->pdo))->get_prices();
        foreach ($prices as $price) {
            if ($this->data['category'] == $price['category']) {
                $this->db_price = $price;
            }
        }
    }

    public function create_invoice()
    {
        // calculate all details
        for ($i = 0; $i < count($this->db_price) - 1; $i++) {
            $this->valid_data[$i] = $this->get_price($i);
        }

        // add custom field
        if (isset($this->data['custom_price']) && isset($this->data['custom_number'])) {
            $this->valid_data['custom'] = $this->calculate_custom_field();
        }

        // get final price
        $total = $this->calculate_final_price();

        // create final array

        // the prices are taken from the DB and "photographed" as the invoice is created
        // can be changed to dynamic invoice generation by removing the *_price things
        // and get the prices from the DB in the display.php file on the UI.
        $data = array(
            'student_id' => $this->data['id'],
            'category' => $this->data['category'],
            'casco_nbr' => $this->data['casco'],
            'lesson_nbr' => $this->data['lesson'],
            'exam_nbr' => $this->data['exam'],
            'oacp_nbr' => $this->data['oacp'],
            'theorical_lesson_nbr' => $this->data['theorical_lesson'],
            'casco_price' => $this->db_price['casco'],
            'lesson_price' => $this->db_price['lesson'],
            'exam_price' => $this->db_price['exam'],
            'oacp_price' => $this->db_price['oacp'],
            'theorical_lesson_price' => $this->db_price['theorical_lesson'],
            'casco_total' => $this->valid_data[0],
            'lesson_total' => $this->valid_data[1],
            'exam_total' =>$this->valid_data[2],
            'oacp_total' => $this->valid_data[3],
            'theorical_lesson_total' => $this->valid_data[4],
            'total' => $total
        );

        return $this->add_invoice($data);
    }

    private function add_invoice(array $data)
    {
        return (new invoicesCreateModel($this->pdo))->create_invoice($data);
    }

    private function get_price(int $id)
    {
        switch ($id) {
            case 0:
                return $this->calculate_casco();
            case 1:
                return $this->calculate_lesson();
            case 2:
                return $this->calculate_exam();
            case 3:
                return $this->calculate_oacp();
            case 4:
                return $this->calculate_theorical_lesson();
        }
    }

    private function calculate_casco()
    {
        return (int)$this->data['casco'] * (int)$this->db_price['casco'];
    }

    private function calculate_lesson()
    {
        return (int)$this->data['lesson'] * (int)$this->db_price['lesson'];
    }

    private function calculate_exam()
    {
        return (int)$this->data['exam'] * (int)$this->db_price['exam'];
    }

    private function calculate_oacp()
    {
        return (int)$this->data['oacp'] * (int)$this->db_price['oacp'];
    }

    private function calculate_theorical_lesson()
    {
        return (int)$this->data['theorical_lesson'] * (int)$this->db_price['theorical_lesson'];
    }

    private function calculate_custom_field()
    {
        return (int)$this->data['custom_price'] * (int)$this->data['custom_number'];
    }

    private function calculate_final_price()
    {
        $total = 0;
        for ($i = 0; $i < count($this->valid_data); $i++) {
            if ($i == 5) break;
            $total += (int)$this->valid_data[$i];
        }

        // add custom field if set
        if (isset($this->valid_data['custom'])) {
            $total += (int)$this->valid_data['custom'];
        }

        return $total;
    }
}