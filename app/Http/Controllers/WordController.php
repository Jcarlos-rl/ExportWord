<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;
use DOMDocument;
use Illuminate\Support\Facades\File;

class WordController extends Controller
{

    public function index()
    {
        return csrf_token();
    }


    public function exportWord($path,$type,$name)
    {
        $path_file = base64_decode($path);

        $file = @file_get_contents($path_file, true);
        if($file != false){

            $data = json_decode($file, true);

            $templateProcessor = new TemplateProcessor('word-template/IPH1.docx');

            /* ----- ----- ----- Valorez encabeado ----- ----- ----- */
            $dataEncabezados = array('EDO0', 'EDO1', 'INST0', 'INST1', 'GOB0', 'GOB1', 'MPIO0', 'MPIO1', 'MPIO2', 'DD0', 'DD1', 'MM0', 'MM1', 'AAAA0', 'AAAA1', 'AAAA2', 'AAAA3', 'HH0', 'HH1', 'MM20', 'MM21');
            foreach($dataEncabezados as $encabezado){
                $templateProcessor->setValue($encabezado,  (count($data['Encabezado'][substr($encabezado,0,-1)])  != 0) ? $data['Encabezado'][substr($encabezado,0,-1)][substr($encabezado,-1)]  : '');
            }

            /* ----- ----- ----- Apartado 1.1 Fecha y Hora de la Puesta a disposición ----- ----- ----- */
            /* if(count($data['PuestaDisposicion']['FechaHora']['Fecha']) != 0){
                foreach($data['PuestaDisposicion']['FechaHora']['Fecha'] as $key=>$fecha){
                    $templateProcessor->setValue('Fecha'.$key, $fecha);
                }
            }else{
                for($i=0; $i<=7; $i++){
                    $templateProcessor->setValue('Fecha'.$i, '');
                }
            } */

            /* ----- ----- ----- Fecha y Hora ----- ----- ----- */
            /* if(count($data['PuestaDisposicion']['FechaHora']['Hora']) != 0){
                foreach($data['PuestaDisposicion']['FechaHora']['Hora'] as $key=>$hora){
                    $templateProcessor->setValue('Hora'.$key, $hora);
                }
            }else{
                for($i=0; $i<=3; $i++){
                    $templateProcessor->setValue('Hora'.$i, '');
                }
            } */

            /* ----- ----- ----- Anexos ----- ----- ----- */
            /* $anexos = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
            if($data['PuestaDisposicion']['AnexosEntregados']['NoEntregan']){
                foreach($anexos as $anexo){
                    if($data['PuestaDisposicion']['AnexosEntregados']['Anexo'.$anexo]['Primero']){
                        $templateProcessor->setValue('Anexo'.$anexo, 'X');
                        foreach($data['PuestaDisposicion']['AnexosEntregados']['Anexo'.$anexo]['Segundo'] as $key=>$numAnexo){
                            $templateProcessor->setValue('Anexo'.$anexo.$key, $numAnexo);
                        }
                    }else{
                        $templateProcessor->setValue('Anexo'.$anexo, '');
                        for($i=0;$i<3;$i++){
                            $templateProcessor->setValue('Anexo'.$anexo.$i, 0);
                        }
                    }
                }
                $templateProcessor->setValue('Anexos', '');
            }else{
                $templateProcessor->setValue('Anexos', 'X');
                foreach($anexos as $key=>$anexo){
                    $templateProcessor->setValue('Anexo'.$anexo, '');
                    for($i=0;$i<3;$i++){
                        $templateProcessor->setValue('Anexo'.$anexo.$i, 0);
                    }
                }
            } */

            /* ----- ----- ----- Anexa Documentacion ----- ----- ----- */
            //echo var_dump($data['PuestaDisposicion']['AnexaDocumentacion']['Band']);

            /* if($data['PuestaDisposicion']['AnexaDocumentacion']['Band']){
                $templateProcessor->setValue('AnexaDocTrue', 'X');
                $templateProcessor->setValue('AnexaDocFalse', '');
            }else{
                $templateProcessor->setValue('AnexaDocTrue', '');
                $templateProcessor->setValue('AnexaDocFalse', 'X');
            }
 */

            $fileName = $name;
            if($type == 1){
                Storage::delete('jsonFiles/'.$name.'.json');
                $templateProcessor->saveAs($fileName.'.docx');
                header("Content-Disposition: attachment; filename='ejemplo.docx'");
                return public_path().'/'.$name.'.docx';

            }else{
                Storage::delete('jsonFiles/'.$name.'.json');
                $templateProcessor->saveAs($fileName.'.docx');
                return response()->file($fileName.'.docx')->deleteFileAfterSend(true);
            }
        }else{
            ($port != '') ? $port : $port=0;
            return redirect()->route('not-file', ['host'=>$host,'file'=>$name_file,'port'=>$port,'https'=>$https]);
        }

    }

    public function notfile($host,$name_file,$port,$https)
    {
        ($port != '' && $port != 0) ? $port = ':'.$port : $port = '';
        $path_file  = $https.'://'.$host.$port.'/PostIPH/Data/'.$name_file.'.json';
        return view('notFile', compact('path_file'));
    }

    public function convertToText($filename) {

        if(isset($filename) && !file_exists($filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($filename);
        $file_ext  = $fileArray['extension'];
        if($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx")
        {
            if($file_ext == "doc") {
                return $this->read_doc($filename);
            } elseif($file_ext == "docx") {
                return $this->read_docx($filename,'word/document.xml');
            }
        } else {
            return "Invalid File Type";
        }
    }

    private function read_doc($filename) {
        $fileHandle = fopen($filename, "r");
        $line = @fread($fileHandle, filesize($filename));
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
            {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
                {
                } else {
                $outtext .= $thisline." ";
                }
            }
            $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }

    public function read_docx($archiveFile, $dataFile) {
        $zip = new ZipArchive;
        if (true === $zip->open($archiveFile)) {
            if (($index = $zip->locateName($dataFile)) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $xml = new DOMDocument();
                $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                return strip_tags($xml->saveXML());
            }
            $zip->close();
        }
        return "";
    }

    public function compareFile($original, $aComparar)
    {
        $file1 = $this->convertToText($original);
        $file2 = $this->convertToText($aComparar);

        return ($file1 == $file2) ? true : false;
    }

    public function receiveData(Request $req){

        $array = array(
            'Encabezado' => array(
                'EDO'  => str_split($req->EDO),
                'INST' => ($req->INST != null) ? str_split($req->INST) : $req->INST,
                'GOB'  => str_split($req->GOB),
                'MPIO' => str_split($req->MPIO),
                'DD'   => str_split($req->DD),
                'MM'   => str_split($req->MM),
                'AAAA' => str_split($req->AAAA),
                'HH'   => str_split($req->HH),
                'MM2'  => str_split($req->MM2)
            ),
            'PuestaDisposicion' => array(
                'FechaHora' => array(
                    'Fecha' => str_split($req->Fecha),
                    'Hora' => str_split($req->Hora),
                    'NoExpediente' => str_split($req->NoExpediente)
                ),
                'AnexosEntregados' => array(
                    'NoEntregan' => $req->NoEntregan
                )
            )
        );

        return $array;

        /* $json = json_encode($array);
        Storage::put('jsonFiles/'.$req->nameFile.'.json', $json);

        $path_file = Storage::path('jsonFiles/'.$req->nameFile.'.json');

        if(isset($req->file)){

            $result = $req->file('file')->storeAs('docsCompare', $req->nameFile.'.docx');
            $pathDoc = Storage::path($result);

            $urlDocOri = $this->exportWord(base64_encode($path_file),1,$req->nameFile);

            $compare = $this->compareFile($urlDocOri, $pathDoc);

            $data['file']    = true;
            $data['compare'] = $compare;
            Storage::delete('docsCompare/'.$req->nameFile.'.docx');
            File::delete(public_path(''.$req->nameFile.'.docx'));
        }else{
            $data['file']    = false;
            $data['urlJson'] = env('APP_URL').'exportWord/'.base64_encode($path_file).'/0/'.$req->nameFile;
        }

        return json_encode($data); */

    }
}
