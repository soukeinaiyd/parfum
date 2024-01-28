<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime, DateTimeZone;

class DatabaseBackupController extends AbstractController
{
    #[Route('/backup-database', name: 'backup_database')]
    public function backupDatabase(): Response
    {
        $this->sauvegarderBaseDeDonnees();

        // Redirigez l'utilisateur vers une page appropriée, par exemple, le tableau de bord EasyAdmin
        return $this->redirectToRoute('admin');
    }

    private function sauvegarderBaseDeDonnees(): void
    {
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('Europe/Paris'));
        $filename = 'backup/' . $date->format('Y-m-d-H-i') . '-' . substr(md5(rand()), 0, 10) . '.sql';

        $command = "mysqldump --user=" . $this->getParameter('database_user') .
            " --password=" . $this->getParameter('database_password') .
            " --host=" . $this->getParameter('database_host') .
            " " . $this->getParameter('database_name') .
            " --result-file={$filename} 2>&1";

        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->addFlash('success', 'La sauvegarde de la base de données a été réalisée avec succès.');
        } else {
            $this->addFlash('danger', 'Erreur lors de la sauvegarde de la base de données. ' . implode("\n", $output));
        }
    }

}
