<?php

use Illuminate\Database\Seeder;

class BoxFilesTableSeeder extends Seeder
{
    public function run()
    {
		factory(App\BoxFile::class, 50)->create();
    }
}
