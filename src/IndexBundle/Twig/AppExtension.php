<?php
/**
 * Created by PhpStorm.
 * User: jaime
 * Date: 26/12/15
 * Time: 08:36 PM
 */

namespace IndexBundle\Twig;


class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('diff', array($this, 'diffDate')),
        );
    }

    public function diffDate($date)
    {
        $now = new \DateTime();

        $diff = $now->diff($date);

        $mes = $diff->m;
        $dias = $diff->d;
        $horas = $diff->h;
        $minutos = $diff->i;
        $segundos = $diff->s;

        if($dias == 0 && $horas == 0 && $minutos == 0){
            return $diff->format("%s segundos");
        }

        if($dias == 0 && $horas == 0 && $minutos == 1){
            return $diff->format("%i minuto");
        }

        if($dias == 0 && $horas == 0 && $minutos > 1){
            return $diff->format("%i minutos");
        }

        if($dias == 0 && $horas == 1){
            return $diff->format("%h hora");
        }

        if($dias == 0 && $horas > 1){
            return $diff->format("%h horas");
        }

        if($dias == 1){
            return $diff->format("%d día");
        }

        if($mes >= 1){
            return $diff->format("+1 mes");
        }

        if($dias > 1){
            return $diff->format("%d días");
        }
    }

    public function getName()
    {
        return "diff_extension";
    }
}