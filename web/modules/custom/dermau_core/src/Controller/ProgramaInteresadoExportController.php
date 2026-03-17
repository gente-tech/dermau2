<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
			'id',
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
			'ID',
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
			$sheet->setCellValue([1, $row_number], (int) $row->id);
			$sheet->setCellValue([2, $row_number], (int) $row->programa_nid);
			$sheet->setCellValue([3, $row_number], (string) $row->programa_title);
			$sheet->setCellValue([4, $row_number], (string) $row->nombre);
			$sheet->setCellValue([5, $row_number], (string) $row->apellido);
			$sheet->setCellValue([6, $row_number], (string) $row->indicativo);
			$sheet->setCellValue([7, $row_number], (string) $row->telefono);
			$sheet->setCellValue([8, $row_number], (string) $row->ciudad);
			$sheet->setCellValue([9, $row_number], (string) $row->profesion);
			$sheet->setCellValue([10, $row_number], (string) $row->mensaje);
			$sheet->setCellValue([11, $row_number], ((int) $row->autorizacion) ? 'Si' : 'No');
			$sheet->setCellValue([12, $row_number], (string) $row->ip);
			$sheet->setCellValue([13, $row_number], (string) $row->user_agent);
			$sheet->setCellValue(
				[14, $row_number],
				!empty($row->created) ? date('Y-m-d H:i:s', (int) $row->created) : ''
			);

			$row_number++;
		}

		foreach (range('A', 'N') as $column_letter) {
			$sheet->getColumnDimension($column_letter)->setAutoSize(TRUE);
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
