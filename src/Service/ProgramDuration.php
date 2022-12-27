<?php

namespace App\Service;

use App\Entity\Program;

class ProgramDuration
{
    public function calculate(Program $program): array
    {
        $seasons = $program->getSeasons();
        $totalDuration = 0;
        foreach ($seasons as $season) {
            $episodes = $season->getEpisodes();
            foreach ($episodes as $episode) {
                $episodeDuration = $episode->getDuration();
                $totalDuration += $episodeDuration;
            }
        }

        $days = floor($totalDuration / 1440);
        $hours = floor(($totalDuration % 1440) / 60);
        $minutes = ($totalDuration % 1440) % 60;

        return [$days, $hours, $minutes];
    }

}