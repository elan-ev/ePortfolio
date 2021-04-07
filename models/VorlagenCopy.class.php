<?php

class VorlagenCopy
{
    public static function copyCourseware(Seminar $master, array $semList)
    {
        $plugin_courseware = PluginManager::getInstance()->getPlugin('Courseware');
        require_once 'public/' . $plugin_courseware->getPluginPath() . '/vendor/autoload.php';

        // create a temporary directory
        $tempDir = $GLOBALS['TMP_PATH'] . '/' . uniqid();
        @mkdir($tempDir);

        //export from master course
        $containerExport = new Courseware\Container(null);
        $containerExport["cid"] = $master->id; //Master cid
        $export = new Mooc\Export\XmlExport($containerExport['block_factory']);
        $coursewareExport = $containerExport["current_courseware"];
        $xml = $export->export($coursewareExport);

        foreach ($containerExport['current_courseware']->getFiles() as $file) {
            if (trim($file['url']) !== '') {
                continue;
            }

            $destination = $tempDir . '/' . $file['id'];
            @mkdir($destination);
            if (file_exists($file['path'])) {
                @copy($file['path'], $destination . '/' . $file['filename']);
            }
        }

        //write export xml-data file
        $destination = $tempDir . "/data.xml";

        $file = fopen($destination, "w+");
        fputs($file, $xml);
        fclose($file);

        foreach ($semList as $user_id => $cid) {

            $root_folder = Folder::findTopFolder($cid);
            $parent_folder = FileManager::getTypedFolder($root_folder->id);

            // create new folder for import
            $request = [
                'name'        => 'Courseware-Import ' . date("d.m.Y", time()),
                'description' => 'folder for imported courseware content'
            ];
            $new_folder = new StandardFolder();
            $new_folder->setDataFromEditTemplate($request);
            $new_folder->user_id = User::findCurrent()->id;
            $courseware_folder = $parent_folder->createSubfolder($new_folder);

            $install_folder = FileManager::getTypedFolder($courseware_folder->id);

            //import in new course
            $containerImport = new Courseware\Container(null);
            $containerImport["cid"] = $cid; //new course cid
            $coursewareImport = $containerImport["current_courseware"];
            $import = new Mooc\Import\XmlImport($containerImport['block_factory']);
            try {
                $import->import($tempDir, $coursewareImport, $install_folder);
            } catch (Exception $e) {

            }
        }
        //delete xml-data file
        self::deleteRecursively($tempDir);
        self::cleanXMLTags();
        self::lockBlocks($master, $semList);
    }

    private static function cleanXMLTags()
    {
        $stmt = DBManager::get()->prepare("UPDATE mooc_fields
            SET json_data = ''
            WHERE json_data = ?");

        $stmt->execute(['"<!DOCTYPE html PUBLIC \"-\/\/W3C\/\/DTD HTML 4.0 Transitional\/\/EN\" \"http:\/\/www.w3.org\/TR\/REC-html40\/loose.dtd\">\n<?xml encoding=\"utf-8\" ?>\n"']);
    }

    private static function deleteRecursively($path)
    {
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                /** @var SplFileInfo $file */
                if (in_array($file->getBasename(), ['.', '..'])) {
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

    private static function lockBlocks(Seminar $master, array $semList)
    {
        $masterBlocks = EportfolioModel::getAllBlocksInOrder($master->id);
        $stmt_read = DBManager::get()->prepare("UPDATE mooc_blocks
            SET approval = ? WHERE id = ?");
        $approval = ['settings' => ['defaultRead' => false]];
        //hier können potentiell beleibige infos von den Vorlagen Blöcken auf die Block-Kopien übertragen werden
        foreach ($semList as $user_id => $cid) {
            $seminarBlocks = EportfolioModel::getAllBlocksInOrder($cid);
            $newBlocks = array_slice($seminarBlocks, -count($masterBlocks));
            //Mapping von neuen Blöcken auf Vorlagen-Blöcke
            for ($i = 0; $i < count($masterBlocks); $i++) {
                BlockInfo::createEntry($cid, $newBlocks[$i], $masterBlocks[$i], $master->id);

                // set default read to false
                $stmt_read->execute([json_encode($approval), $newBlocks[$i]]);
            }
        }
    }
}