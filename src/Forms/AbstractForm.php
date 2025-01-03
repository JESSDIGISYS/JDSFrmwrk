<?php

namespace JDS\Forms;

class AbstractForm
{

    public function generateSlug(string $title): string
    {
        // Convert title to lowercase, trim spaces, replace special characters with hyphens, and remove extra hyphens
        return preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($title))));
    }


}