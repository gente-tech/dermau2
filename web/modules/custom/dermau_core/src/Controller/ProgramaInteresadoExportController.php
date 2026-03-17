<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProgramaInteresadoExportController extends ControllerBase
{
	protected Connection $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}

	public static function create(ContainerInterface $container)
	{
		return new static(
			$container->get('database')
		);
	}

	public function download()
	{
		$query = $this->database->select('dermau_programa_interesado', 'dpi');
		$query->fields('dpi', [
			'programa_nid',
			'programa_title',
			'nombre',
			'apellido',
			'indicativo',
			'telefono',
			'ciudad',
			'profesion',
			'mensaje',
			'autorizacion',
			'ip',
			'user_agent',
			'created',
		]);
		$query->orderBy('created', 'DESC');

		$rows = $query->execute()->fetchAll();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Inscripciones');

		$headers = [
			'Programa NID',
			'Programa',
			'Nombre',
			'Apellido',
			'Indicativo',
			'Telefono',
			'Ciudad',
			'Profesion',
			'Mensaje',
			'Autorizacion',
			'IP',
			'User Agent',
			'Fecha de registro',
		];

		$column = 1;
		foreach ($headers as $header) {
			$sheet->setCellValue([$column, 1], $header);
			$column++;
		}

		$row_number = 2;
		foreach ($rows as $row) {
			$sheet->setCellValue([1, $row_number], (int) $row->programa_nid);
			$sheet->setCellValue([2, $row_number], (string) $row->programa_title);
			$sheet->setCellValue([3, $row_number], (string) $row->nombre);
			$sheet->setCellValue([4, $row_number], (string) $row->apellido);
			$sheet->setCellValue([5, $row_number], (string) $row->indicativo);
			$sheet->setCellValue([6, $row_number], (string) $row->telefono);
			$sheet->setCellValue([7, $row_number], (string) $row->ciudad);
			$sheet->setCellValue([8, $row_number], (string) $row->profesion);
			$sheet->setCellValue([9, $row_number], (string) $row->mensaje);
			$sheet->setCellValue([10, $row_number], ((int) $row->autorizacion) ? 'Si' : 'No');
			$sheet->setCellValue([11, $row_number], (string) $row->ip);
			$sheet->setCellValue([12, $row_number], (string) $row->user_agent);
			$sheet->setCellValue(
				[13, $row_number],
				!empty($row->created) ? date('Y-m-d H:i:s', (int) $row->created) : ''
			);

			$row_number++;
		}

		$last_row = max(1, $sheet->getHighestRow());
		$last_column = 'M';

		/*
    -----------------------------------------
    Estilos encabezado
    -----------------------------------------
    */
		$sheet->getStyle('A1:M1')->applyFromArray([
			'font' => [
				'bold' => TRUE,
				'size' => 12,
				'color' => ['rgb' => 'FFFFFF'],
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => ['rgb' => '1F4E78'],
			],
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER,
				'vertical' => Alignment::VERTICAL_CENTER,
			],
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN,
					'color' => ['rgb' => 'D9E2F3'],
				],
			],
		]);

		/*
    -----------------------------------------
    Estilos cuerpo de la tabla
    -----------------------------------------
    */
		if ($last_row >= 2) {
			$sheet->getStyle('A2:M' . $last_row)->applyFromArray([
				'font' => [
					'size' => 11,
					'color' => ['rgb' => '1F1F1F'],
				],
				'alignment' => [
					'vertical' => Alignment::VERTICAL_CENTER,
				],
				'borders' => [
					'allBorders' => [
						'borderStyle' => Border::BORDER_THIN,
						'color' => ['rgb' => 'EAEAEA'],
					],
				],
			]);
		}

		/*
    -----------------------------------------
    Alineación por columnas
    -----------------------------------------
    */
		$sheet->getStyle('A:M')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

		$sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('B:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('E:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('G:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('K:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('L:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('M:M')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		/*
    -----------------------------------------
    Ajustes de texto largo
    -----------------------------------------
    */
		$sheet->getStyle('I:I')->getAlignment()->setWrapText(TRUE);
		$sheet->getStyle('L:L')->getAlignment()->setWrapText(TRUE);

		/*
    -----------------------------------------
    Altura de filas
    -----------------------------------------
    */
		$sheet->getRowDimension(1)->setRowHeight(26);

		for ($i = 2; $i <= $last_row; $i++) {
			$sheet->getRowDimension($i)->setRowHeight(22);
		}

		/*
    -----------------------------------------
    Anchos de columna
    -----------------------------------------
    */
		$sheet->getColumnDimension('A')->setWidth(15);
		$sheet->getColumnDimension('B')->setWidth(35);
		$sheet->getColumnDimension('C')->setWidth(20);
		$sheet->getColumnDimension('D')->setWidth(20);
		$sheet->getColumnDimension('E')->setWidth(12);
		$sheet->getColumnDimension('F')->setWidth(18);
		$sheet->getColumnDimension('G')->setWidth(22);
		$sheet->getColumnDimension('H')->setWidth(22);
		$sheet->getColumnDimension('I')->setWidth(40);
		$sheet->getColumnDimension('J')->setWidth(15);
		$sheet->getColumnDimension('K')->setWidth(18);
		$sheet->getColumnDimension('L')->setWidth(45);
		$sheet->getColumnDimension('M')->setWidth(22);

		/*
    -----------------------------------------
    Filtros y congelar encabezado
    -----------------------------------------
    */
		$sheet->setAutoFilter('A1:M1');
		$sheet->freezePane('A2');

		/*
    -----------------------------------------
    Color alterno en filas
    -----------------------------------------
    */
		for ($i = 2; $i <= $last_row; $i++) {
			if ($i % 2 === 0) {
				$sheet->getStyle('A' . $i . ':M' . $i)->applyFromArray([
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => ['rgb' => 'F7FAFC'],
					],
				]);
			}
		}

		$file_system = \Drupal::service('file_system');
		$temporary_path = $file_system->realpath('temporary://');

		if (!$temporary_path) {
			throw new \RuntimeException('No fue posible resolver el directorio temporal.');
		}

		$filename = 'registros_programa_interesado_' . date('Y-m-d_H-i-s') . '.xlsx';
		$full_path = $temporary_path . DIRECTORY_SEPARATOR . $filename;

		$writer = new Xlsx($spreadsheet);
		$writer->save($full_path);

		$spreadsheet->disconnectWorksheets();
		unset($spreadsheet);

		$response = new BinaryFileResponse($full_path);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$filename
		);
		$response->headers->set(
			'Content-Type',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		);
		$response->deleteFileAfterSend(TRUE);

		return $response;
	}
}
