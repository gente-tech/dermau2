<?php

namespace Drupal\dermau_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

		$rows = $query->execute()->fetchAllAssoc('id');

		$response = new StreamedResponse(function () use ($rows) {
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
				'Teléfono',
				'Ciudad',
				'Profesión',
				'Mensaje',
				'Autorización',
				'IP',
				'User Agent',
				'Fecha de registro',
			];

			$column = 1;
			foreach ($headers as $header) {
				$sheet->setCellValueByColumnAndRow($column, 1, $header);
				$column++;
			}

			$row_number = 2;

			foreach ($rows as $row) {
				$sheet->setCellValueByColumnAndRow(1, $row_number, (int) $row->id);
				$sheet->setCellValueByColumnAndRow(2, $row_number, (int) $row->programa_nid);
				$sheet->setCellValueByColumnAndRow(3, $row_number, (string) $row->programa_title);
				$sheet->setCellValueByColumnAndRow(4, $row_number, (string) $row->nombre);
				$sheet->setCellValueByColumnAndRow(5, $row_number, (string) $row->apellido);
				$sheet->setCellValueByColumnAndRow(6, $row_number, (string) $row->indicativo);
				$sheet->setCellValueByColumnAndRow(7, $row_number, (string) $row->telefono);
				$sheet->setCellValueByColumnAndRow(8, $row_number, (string) $row->ciudad);
				$sheet->setCellValueByColumnAndRow(9, $row_number, (string) $row->profesion);
				$sheet->setCellValueByColumnAndRow(10, $row_number, (string) $row->mensaje);
				$sheet->setCellValueByColumnAndRow(11, $row_number, ((int) $row->autorizacion) ? 'Sí' : 'No');
				$sheet->setCellValueByColumnAndRow(12, $row_number, (string) $row->ip);
				$sheet->setCellValueByColumnAndRow(13, $row_number, (string) $row->user_agent);
				$sheet->setCellValueByColumnAndRow(
					14,
					$row_number,
					!empty($row->created) ? date('Y-m-d H:i:s', (int) $row->created) : ''
				);

				$row_number++;
			}

			foreach (range('A', 'N') as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			}

			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		});

		$filename = 'registros_programa_interesado_' . date('Y-m-d_H-i-s') . '.xlsx';

		$response->headers->set(
			'Content-Type',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		);

		$response->headers->set(
			'Content-Disposition',
			$response->headers->makeDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT,
				$filename
			)
		);

		$response->headers->set('Cache-Control', 'max-age=0, no-cache, no-store, must-revalidate');
		$response->headers->set('Pragma', 'public');

		return $response;
	}
}
