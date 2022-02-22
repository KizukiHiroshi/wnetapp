<?php

namespace Database\Factories\Zero;

use App\Models\Zero\Concern;
use App\Models\Common\JobType;
use Database\Seeders\JobtypeSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcernFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concern::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $jobtype_id = Jobtype::all()->random(1)[0]->id;
        return [
            'jobtype_id' => $jobtype_id,
            'name' => $this->faker->realText($maxNbChars = 40),
            'content' => $this->faker->realText($maxNbChars = 80),
            'importance' => 'C',
            'priority' => 0,
            'solution' => $this->faker->realText($maxNbChars = 80),
            'is_solved' => $this->faker->boolean,
            'created_at' => now(),
            'updated_at' => now(),
            'updated_by' => '本部 杵築'
        ];
    }
}
