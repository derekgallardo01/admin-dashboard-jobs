<?php 
/**
 * Function to render create and render the PDF with the invoice
 */
function generatePDFInvoice() {
        $response = array(
            'status' => 'error',
            'message' => __('PDF Invoices were not generated', self::LANG)
        );

        if( empty($_POST['users']) ) {
            $response = array(
                'status' => 'error',
                'message' => __('The users list is empty', self::LANG)
            );
            echo json_encode($response);
            die();
        }
        $users = $_POST['users'];
        $lead_id = $_POST['lead'];
        $priceMinute = $_POST['priceMinute'];

        $result = $status = array();
        $date = date("n - j - Y");
        $invoice_number = "000XX";

        $ipsum = <<<IPSUM
Ahh That The Spot Massage
info@ahhthatsthespotmassage.com
PO BOX 381054 Miami, Florida, 33138
United States


DATE: {date}
INVOICE #: {invoice-number}


Bill to {user-name}
{user-email}
IPSUM;
        
        $messageItem = "Event Massage #{num}:\nTotal: {simbol} {price} USD\n{description}\n";

        foreach ($users as $u) {

            if(is_object($u)) {
                $u = get_object_vars($u);
            }
            $user = get_user_by('id', $u['userid']);
            $total_price = 0;

            foreach ($u['items'] as $i=>&$item) {
                if(is_object($item)) {
                    $item = get_object_vars($item);
                }

                $item['price'] = $item['minutes']*$priceMinute;

                $price_message .= str_replace(array('{num}','{simbol}','{price}','{description}'), array($i+1,Admin_Jobs::$symbol_price,$item['price'],$item['description']), $messageItem)."\n";
                
                $total_price += $item['price'];
            }

            if($total_price <= 0) {
                $cstatus = array(
                    'status' => 'error',
                    'user_id' => $user->ID,
                    'user_email' => $user->user_email,
                    'message' => __('The invoice was not generated due to the amount minutes is 0', self::LANG)
                );
                $status[] = $cstatus;
                continue;
            }

            $price_message .= str_replace(array('{simbol}','{price}'), array(self::$symbol_price,$total_price), __('Total: {simbol} {price} USD'))."\n";
            
            $price_message = str_replace('<br/>', "\n", $price_message);

            $name_client = empty($user->first_name) ? $user->user_email  : $user->first_name;
            $text = str_replace(array('{user-name}','{user-email}','{date}','{invoice-number}'), 
                                array($name_client, $user->user_email, $date, $invoice_number), $ipsum);

            $pdf = new FPDF();
            $pdf->AddPage();
            

            $pdf->SetMargins(18, 16, 18);

            $pdf->Image(ADMJOBS_DIR.'/top_image.png',19);
            
            $pdf->Ln(5);
            $pdf->SetFont('Arial','B',16);    
            $pdf->Cell(0,10,'Invoice',0,1,'C');
            
            $pdf->SetX(17);

            $pdf->SetFont('Arial','',13);
            $pdf->Write(8, $text, '', true);

            $pdf->Ln(10);

            $pdf->SetFont('Arial','B',14);
            $pdf->Write(8, __('List the events massage service to pay:',self::LANG), '', true);

            $pdf->Ln();

            $pdf->SetFont('Arial','',13);
            $pdf->Write(8, $price_message, '', true);
            
            $file = ADMJOBS_DIR.'/invoices/Invoice_'.$user->ID.'_'.$lead_id.'.pdf';

            if(file_exists($file)) {
                unlink($file);
            }

            $result[] = array(
                'file' => $file,
                'user' => $user->ID,
                'items' => $u['items']
            );

            $pdf->Output($file,'F');
            $cstatus = array(
                'status' => 'OK',
                'user_id' => $user->ID,
                'user_email' => $user->user_email
            );
            if(file_exists($file)) {
                $cstatus['message'] = __('The invoice has been generated', self::LANG);
            } else {
                $cstatus['message'] = __('The invoice was not generated', self::LANG);
            }
            $status[] = $cstatus;
        }

        if(count($result) > 0) {
            $response = array(
                'status' => 'OK',
                'message' => __('Invoice generated successfully', self::LANG),
                'datos' => $result
            );
        }
        $response['resultsStatus'] = $status;

        return $response;
}
