<?php

namespace Amulen\PaymentBundle\Controller\Paypal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationController extends Controller {

    /**
     * Test IPN
     *
     * #### Response ok ####
     * List(PaymentLog)
     *
     * #### Response error ####
     * {
     * "success": false,
     * "message": "Error message"
     * }
     *
     * #### Response user not logged ####
     * {
     *  "code": 401,
     *  "message": "Invalid credentials" or "Invalid JWT token"
     * }
     *
     *
     * @ApiDoc(
     *  description="Test IPN",
     *  section="Cloudlance Payment IPN",
     *  authentication = true,
     *  headers= {
     *      {
     *          "name"= "Authorization",
     *          "required"="true",
     *          "description"= "Bearer {token}"
     *      }
     *  },
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when the user unauthorized",
     *     }
     * )
     *
     * @Route("/amulen_payment/notification/paypal", name="amulen_payment_notification_paypal")
     * @Method("POST")
     */
    public function receiveAction(Request $request) {

        /*   $publicFilesPath = $this->container->getParameter('public_files_path');
          if (!is_dir($publicFilesPath)) {
          mkdir($publicFilesPath, 0755, true);
          }
          $fullPath = $publicFilesPath . 'pruebaPaypal.txt';
          $ifp = fopen($fullPath, "wb");
          fwrite($ifp, "Fecha: " . date("D M d, Y G:i"));
          fwrite($ifp, "\n" . 'Recibido: ' . json_encode($request->getContent()));
          fclose($ifp);
         */
        // 1. Get data from paypal and convert to array
        $raw_post_data = $request->getContent();
        $dataNotification = explode('&', $raw_post_data);

        // 2. Se verifica el llamado de paypal
        $dataNotificationArray = array();
        foreach ($dataNotification as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $dataNotificationArray[$keyval[0]] = urldecode($keyval[1]);
        }
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($dataNotificationArray as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }
        $urlPaypal = 'https://www.paypal.com/cgi-bin/webscr';
        if ($dataNotificationArray['test_ipn'] == '1') {
            $urlPaypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        $ch = curl_init($urlPaypal);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        if (!($res = curl_exec($ch))) {
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // 3. Verificar que sea un llamado de paypal verificado e implementar acordemente
        if (strcmp($res, "VERIFIED") == 0) {
            $paypalService = $this->get('amulen_payment.paypal.service');
            $paypalService->verifyNotification($dataNotificationArray);
            return new Response("Request Verified.");
        } else if (strcmp($res, "INVALID") == 0) {
            return new Response("Request Not Verified.");
        }
    }

}
