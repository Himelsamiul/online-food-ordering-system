<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DeliveryMan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderExportController extends Controller
{
    public function export(Request $request)
    {
        // ================= QUERY (SAME AS INDEX FILTERS) =================
        $query = Order::with(['deliveryRun.deliveryMan']);

        if ($request->filled('order_number')) {
            $query->where('order_number', $request->order_number);
        }

        if ($request->filled('customer_name')) {
            $query->where('name', $request->customer_name);
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('delivery_man_id')) {
            $query->whereHas('deliveryRun', function ($q) use ($request) {
                $q->where('delivery_man_id', $request->delivery_man_id);
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59',
            ]);
        }

        $orders = $query->latest()->get();

        // ================= SPREADSHEET =================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ================= HEADERS =================
        $headers = [
            'SL',
            'Order No',
            'Transaction No',
            'Customer Name',
            'Phone',
            'Address',
            'Delivery Man',
            'Payment Method',
            'Payment Status',
            'Order Status',
            'Total Amount',
            'Order Date',
        ];

        $sheet->fromArray($headers, null, 'A1');

        // ================= DATA =================
        $row = 2;
        $sl  = 1;

        foreach ($orders as $order) {
            $sheet->setCellValue("A{$row}", $sl++);
            $sheet->setCellValue("B{$row}", $order->order_number);
            $sheet->setCellValue("C{$row}", $order->transaction_number ?? '-');
            $sheet->setCellValue("D{$row}", $order->name);
            $sheet->setCellValue("E{$row}", $order->phone);
            $sheet->setCellValue("F{$row}", $order->address);

            $sheet->setCellValue(
                "G{$row}",
                optional(optional($order->deliveryRun)->deliveryMan)->name ?? 'Not Assigned'

            );

            $sheet->setCellValue("H{$row}", strtoupper($order->payment_method));
            $sheet->setCellValue("I{$row}", ucfirst($order->payment_status));
            $sheet->setCellValue("J{$row}", ucfirst(str_replace('_', ' ', $order->order_status)));
            $sheet->setCellValue("K{$row}", $order->total_amount);
            $sheet->setCellValue("L{$row}", $order->created_at->format('d M Y h:i A'));

            $row++;
        }

        // ================= AUTO WIDTH =================
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ================= DOWNLOAD RESPONSE =================
        $fileName = 'orders_export_' . now()->format('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

}
