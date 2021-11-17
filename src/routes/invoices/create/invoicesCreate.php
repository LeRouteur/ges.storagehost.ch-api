<?php

namespace Invoices;

class invoicesCreate
{
    public function __construct()
    {

    }

    public function calculate_casco()
    {
        // B : 80 CHF
        // C : 150 CHF
        // D : 150 CHF
        // CE : 200 CHF
    }

    public function calculate_lesson()
    {
        // B : 95 CHF
        // C : 170 CHF
        // D : 190 CHF
        // CE : 170 CHF
    }

    public function calculate_exam()
    {
        // B : 100 CHF
        // C : 350 CHF
        // D : 500 CHF
        // CE : 350 CHF
    }

    public function calculate_final_price()
    {
        // casco + prix lesson * nbr lesson + prix examen
    }
}