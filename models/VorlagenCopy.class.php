<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class VorlagenCopy{
    
    
    
    public function copyCourseware(Seminar $master, array $semList)
    {
        
        $plugin_courseware = PluginManager::getInstance()->getPlugin('Courseware');
        require_once 'public/' . $plugin_courseware->getPluginPath() . '/vendor/autoload.php';

         // create a temporary directory
        $tempDir = $GLOBALS['TMP_PATH'].'/'.uniqid();
        mkdir($tempDir);
        
        //export from master course
        $containerExport =  new Courseware\Container(NULL);
        $containerExport["cid"] = $master->id; //Master cid
        $export = new Mooc\Export\XmlExport($containerExport['block_factory']);
        $coursewareExport = $containerExport["current_courseware"];
        $xml = $export->export($coursewareExport);
        
        foreach ($containerExport['current_courseware']->getFiles() as $file) {
            if (trim($file['url']) !== '') {
                continue;
            }

            $destination = $tempDir . '/' . $file['id'];
            mkdir($destination);
            copy($file['path'], $destination.'/'.$file['filename']);
        }

        //write export xml-data file
        $destination = $tempDir."/data.xml";
        $file = fopen($destination, "w+");
        fputs($file, $xml);
        fclose($file);

        foreach($semList as $user_id => $cid){
            //import in new course
            $containerImport =  new Courseware\Container(NULL);
            $containerImport["cid"] = $cid; //new course cid
            $coursewareImport = $containerImport["current_courseware"];
            $import =  new Mooc\Import\XmlImport($containerImport['block_factory']);
            $import->import($tempDir, $coursewareImport);
        }
        //delete xml-data file
        self::deleteRecursively($tempDir);
    }


    private function deleteRecursively($path)
    {
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                /** @var SplFileInfo $file */
                if (in_array($file->getBasename(), array('.', '..'))) {
                    continue;
                }

                if ($file->isFile() || $file->isLink()) {
                    unlink($file->getRealPath());
                } else if ($file->isDir()) {
                    rmdir($file->getRealPath());
                }
            }

            rmdir($path);
        } else if (is_file($path) || is_link($path)) {
            unlink($path);
        }
    }
    
    
}