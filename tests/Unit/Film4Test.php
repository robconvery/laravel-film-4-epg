<?php
/**
 * Class Film4Test
 *
 * @author robconvery <robconvery@me.com>
 */

namespace Robconvery\laravelFilm4Epg\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use Robconvery\laravelFilm4Epg\Film4;

class Film4Test extends TestCase
{
    /**
     * @test
     * @group get_films
     * @dataProvider dates
     */
    public function get_films($day)
    {
        // Arrange
        $date = Carbon::now()->addDay($day);

        // Act
        $data = Film4::films($date);

        // Assert
        $this->assertInstanceOf(Collection::class, $data);

        echo "\n" . $date->format('l jS'). "\n";
        $data->map(function ($film) {
            echo $film['time'] . ' ' .  $film['title'] . "\n";
        });
    }

    public function dates()
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
            [9]
        ];
    }
}
