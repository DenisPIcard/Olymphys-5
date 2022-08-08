<?php
// src/Utils/ExcelCreate.php
namespace App\Utils;

use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelCreate
{
    /**
     * @throws Exception
     */
    public function excelfrais($edition, $data, $nblig): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("Frais de la " . $edition . "ème édition ")
            ->setSubject("Frais")
            ->setDescription("Frais avec Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        try {
            $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);
        } catch (Exception $e) {
        }

        try {
            $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        } catch (Exception $e) {
        }

        $borderArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $centerArray = [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(13);
        $sheet->getColumnDimension('I')->setWidth(8);

        try {
            $sheet->mergeCells('C1:H1');
        } catch (Exception $e) {
        }
        $sheet->setCellValue('C1', 'Frais du Comité des OdPF pour la ' . $edition . 'ème édition');
        $sheet->getStyle('C1:H1')->applyFromArray($borderArray)
            ->getAlignment()->applyFromArray($centerArray);
        $sheet->setCellValue('A4', 'date');
        $sheet->setCellValue('B4', 'désignation de la dépense');
        $sheet->getStyle('A4')->applyFromArray($borderArray);
        $sheet->getStyle('B4')->applyFromArray($borderArray);
        try {
            $sheet->mergeCells('C3:H3');
        } catch (Exception $e) {
        }
        $sheet->setCellValue('C3', 'Sommes dépensées par catégorie');
        $sheet->getStyle('C3:H3')->applyFromArray($borderArray)
            ->getAlignment()->applyFromArray($centerArray);
        $sheet->setCellValue('C4', 'Déplacements');
        $sheet->setCellValue('D4', 'Repas');
        $sheet->setCellValue('E4', 'Fourniture');
        $sheet->setCellValue('F4', 'Frais postaux');
        $sheet->setCellValue('G4', 'Impressions');
        $sheet->setCellValue('H4', 'Autres');

        for ($lettre = 'A'; $lettre < 'I'; $lettre++) {
            $sheet->getStyle($lettre . '4')->applyFromArray($borderArray)
                ->getAlignment()->applyFromArray($centerArray);
        }

        $total_fourn = 0;
        $total_poste = 0;
        $total_impress = 0;
        $total_depl = 0;
        $total_repas = 0;
        $total_autr = 0;
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

        for ($i = 1; $i < $nblig + 1; $i++) {
            $k = $i + 4;
            $date = $data['date' . $i]->getTimestamp();
            $result = strftime('%d-%b-%g', $date);
            $sheet->setCellValue('A' . $k, $result);
            $design = $data['designation' . $i];
            $sheet->setCellValue('B' . $k, $design);
            $depl = $data['deplacement' . $i];
            $sheet->setCellValue('C' . $k, $depl);
            $repas = $data['repas' . $i];
            $sheet->setCellValue('D' . $k, $repas);
            $fourn = $data['fournitures' . $i];
            $sheet->setCellValue('E' . $k, $fourn);
            $poste = $data['poste' . $i];
            $sheet->setCellValue('F' . $k, $poste);
            $impress = $data['impressions' . $i];
            $sheet->setCellValue('G' . $k, $impress);
            $autr = $data['autres' . $i];
            $sheet->setCellValue('H' . $k, $autr);
            $sheet->getStyle('A' . $k . ':H' . $k)->getAlignment()->applyFromArray($centerArray);
            for ($lettre = 'A'; $lettre < 'I'; $lettre++) {
                $sheet->getStyle($lettre . $k . ':' . $lettre . $k)->applyFromArray($borderArray);
            }
            $total_fourn = $total_fourn + $fourn;
            $total_poste = $total_poste + $poste;
            $total_impress = $total_impress + $impress;
            $total_depl = $total_depl + $depl;
            $total_repas = $total_repas + $repas;
            $total_autr = $total_autr + $autr;
        }
        $k++;
        $sheet->setCellValue('B' . $k, 'TOTAL PAR CATÉGORIE');
        $sheet->setCellValue('C' . $k, $total_depl);
        $sheet->setCellValue('D' . $k, $total_repas);
        $sheet->setCellValue('E' . $k, $total_fourn);
        $sheet->setCellValue('F' . $k, $total_poste);
        $sheet->setCellValue('G' . $k, $total_impress);
        $sheet->setCellValue('H' . $k, $total_autr);
        for ($j = 'B'; $j < 'I'; $j++) {
            $sheet->getStyle($j . $k)->applyFromArray($borderArray)
                ->getAlignment()->applyFromArray($centerArray);
        }
        $k++;
        $sheet->setCellValue('B' . $k, 'TOTAL PAR POSTE');
        $poste1 = $total_depl + $total_repas;
        try {
            $sheet->mergeCells('C' . $k . ':D' . $k);
        } catch (Exception $e) {
        }
        $sheet->setCellValue('C' . $k, $poste1);
        $poste2 = $total_fourn + $total_poste;
        $sheet->mergeCells('E' . $k . ':F' . $k);
        $sheet->setCellValue('E' . $k, $poste2);
        $poste3 = $total_impress;
        $sheet->setCellValue('G' . $k, $poste3);
        $poste4 = $total_autr;
        $sheet->setCellValue('H' . $k, $poste4);

        for ($j = 'B'; $j < 'I'; $j++) {
            $sheet->getStyle($j . $k)->applyFromArray($borderArray)
                ->getAlignment()->applyFromArray($centerArray);
        }
        $k++;
        $sheet->setCellValue('B' . $k, 'TOTAL À REMBOURSER');
        $total = $poste1 + $poste2 + $poste3 + $poste4;
        $sheet->mergeCells('C' . $k . ':H' . $k);
        $sheet->setCellValue('C' . $k, $total);
        for ($j = 'B'; $j < 'H'; $j++) {
            $sheet->getStyle($j . $k)->getAlignment()->applyFromArray($centerArray);
        }
        $sheet->getStyle('B' . $k . ':H' . $k)->applyFromArray($borderArray);

        $k++;
        $k++;
        $DebutCadre = $k;
        //$nom=$user->getLastname();
        $nom = 'essai';
        $sheet->setCellValue('A' . $k, 'Nom');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        $sheet->setCellValue('B' . $k, $nom);
        $k++;
        //$prenom=$user->getFirstname();
        $sheet->setCellValue('A' . $k, 'Prénom');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        //$sheet->setCellValue('B'.$k, $prenom);
        $k++;
        //$adresse=$user->getAdresse();
        $sheet->mergeCells('B' . $k . ':C' . $k);
        $sheet->setCellValue('A' . $k, 'Adresse');
        //$sheet->setCellValue('B'.$k, $adresse);
        $k++;

        $sheet->setCellValue('A' . $k, 'Code');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        //$sheet->setCellValue('B'.$k, $user->getCode());
        $k++;
        $sheet->setCellValue('A' . $k, 'Ville');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        //$sheet->setCellValue('B'.$k, $user->getVille());
        $k++;

        //$email=$user->getEmail();
        $sheet->setCellValue('A' . $k, 'Email');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        //$sheet->setCellValue('B'.$k, $email);
        $k++;
        //$phone=$user->getPhone();
        $sheet->setCellValue('A' . $k, 'Téléphone');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        //$sheet->setCellValue('B'.$k, $phone);

        $k++;

        $iban = $data['iban1'];
        for ($i = 2; $i < 8; $i++) {
            $iban .= ' ' . $data['iban' . $i];
        }

        $sheet->setCellValue('A' . $k, 'IBAN');
        $sheet->mergeCells('B' . $k . ':C' . $k);
        $sheet->setCellValue('B' . $k, $iban);

        $sheet->getStyle('A' . $DebutCadre . ':B' . $k)->getAlignment()->applyFromArray($centerArray);
        for ($loop = $DebutCadre; $loop <= $k; $loop++) {
            $sheet->getStyle('A' . $loop)->applyFromArray($borderArray);
            $sheet->getStyle('B' . $loop . ':C' . $loop)->applyFromArray($borderArray);
        }


        $sheet->mergeCells('E' . $DebutCadre . ':H' . $DebutCadre);
        $sheet->setCellValue('E' . $DebutCadre, 'Les justificatifs sont à faire parvenir');

        $k = $DebutCadre;
        $k++;
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $sheet->setCellValue('E' . $k, 'par voie postale à');
        $k++;
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $k++;
        $sheet->setCellValue('E' . $k, 'Marine JADOULE, Société Française de Physique');
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $k++;
        $sheet->setCellValue('E' . $k, '33 rue Croulebarbe');
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $k++;
        $sheet->setCellValue('E' . $k, '75013 PARIS');
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $k++;
        $sheet->setCellValue('E' . $k, "ou, s'il s'agit de documents pdf");
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $k++;
        $sheet->setCellValue('E' . $k, 'par mel : compta@sfpnet.fr');
        $sheet->mergeCells('E' . $k . ':H' . $k);
        $FinCadre = $k;

        $sheet->getStyle('E' . $DebutCadre . ':H' . $FinCadre)->applyFromArray($borderArray)
            ->getAlignment()->applyFromArray($centerArray);

        $k++;
        $k++;
        $sheet->setCellValue('F' . $k, "Date d'envoi");
        $auj = strftime('%d-%m-%g');
        $sheet->setCellValue('G' . $k, $auj);
        $sheet->getStyle('F' . $k . ':G' . $k)->applyFromArray($borderArray);

        $nomfic = 'frais_' . $nom . '_' . $auj . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $nomfic);
        header('Cache-Control: max-age=0');


        $writer = new Xls($spreadsheet);
        //$writer = new Xls;
        //$adr='./Frais_comite/';
        //    $fichier=$adr.$nomfic;

        //$writer->save($fichier);

        $writer->save('php://output');

        $fichier = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $fichier->headers->set('Content-Type', 'application/vnd.ms-excel');
        $fichier->headers->set('Content-Disposition', 'attachment;filename=' . $nomfic);
        $fichier->headers->set('Cache-Control', 'max-age=0');
        return $fichier;

        //return $fichier ;
    }
}

