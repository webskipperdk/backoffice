<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\BasicRum\Beacon\Catcher\Storage\File;
use App\BasicRum\Beacon\Importer\Process;

use App\BasicRum\Visit\Calculator;
use App\BasicRum\Visit\Data\Persist;

use App\BasicRum\Stats\LastBlockingResourceCalculator;

class CronController extends AbstractController
{

    /**
     * @Route("/cron/run_all", name="cron_run_all")
     */
    public function runAll()
    {
        ini_set('memory_limit', '-1');

        $data = [];

        //Create Bundle from Raw beacons
        $start = microtime(true);
        $count = $this->_generateBundleFromRawBeacons();
        $duration = microtime(true) - $start;

        $data['generate_bundle_bundle_from_raw'] = [
            'duration' => $duration,
            'count' => $count
        ];

        //Import beacons bundle
        $start = microtime(true);
        $count = $this->_importBeaconsFromBundle();
        $duration = microtime(true) - $start;

        $data['import_beacons_bundle'] = [
            'duration' => $duration,
            'count' => $count
        ];

        //Generate Visits
        $start = microtime(true);
        $count = $this->_generateVisits();
        $duration = microtime(true) - $start;

        $data['generate_visits'] = [
            'duration' => $duration,
            'count' => $count
        ];

        //Last Blocking Resource
        $start = microtime(true);
        $count = $this->_calculateLastBlockingResource();
        $duration = microtime(true) - $start;

        $data['last_blocking_resource'] = [
            'duration' => $duration,
            'count' => $count
        ];

        //Last Blocking Resource
        $start = microtime(true);
        $count = $this->_archiveBeaconsBundle();
        $duration = microtime(true) - $start;

        $data['archive_beacon_bundles'] = [
            'duration' => $duration,
            'count' => $count
        ];

        $response = new Response(
            json_encode(
                $data
            )
        );

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @return int
     */
    private function _generateBundleFromRawBeacons() : int
    {
        $storage = new File();

        return $storage->generateBundleFromRawBeacons();
    }

    /**
     * @return int
     */
    private function _importBeaconsFromBundle() : int
    {
        $storage = new File();

        $bundleFiles = $storage->getBundleFilePaths();

        $count = 0;

        foreach ($bundleFiles as $file) {
            $reader = new Process\Reader\CatcherService($file);
            $process = new Process($this->getDoctrine());


            $count += $process->runImport($reader);
        }

        return $count;
    }

    /**
     * @return int
     */
    private function _generateVisits() : int
    {
        $calculator = new Calculator($this->getDoctrine());
        $visits = $calculator->calculate();

        $persist = new Persist($this->getDoctrine());
        $persist->saveVisits($visits);

        return count($visits);
    }

    /**
     * @return int
     */
    private function _calculateLastBlockingResource() : int
    {
        $calculator = new LastBlockingResourceCalculator($this->getDoctrine());
        return $calculator->calculate();
    }

    /**
     * @return int
     */
    private function _archiveBeaconsBundle() : int
    {
        $storage = new File();

        return $storage->archiveBundles();
    }

}
