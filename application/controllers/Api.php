<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public $url = 'https://api.carbook.pro/';

    public function index()
    {
        echo 'API ONECAR';
    }

    function _request($url, $request_type = 'GET', $data = [], $nodecode = false)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // OLD TOKEN - eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiIxZThmOTM0MS0xMTBhLTRjMTQtYmMxMy0wZDRiMTg5NGY1YTkiLCJpYXQiOjE2OTg4Mjc5NTl9.KyFWAZt33f5tKZCX5koZymvg4xb0POIzp_xDW46ZTfA

        $headers[] = 'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzaWQiOiIxZThmOTM0MS0xMTBhLTRjMTQtYmMxMy0wZDRiMTg5NGY1YTkiLCJpYXQiOjE2OTg4Mjc5NTl9.KyFWAZt33f5tKZCX5koZymvg4xb0POIzp_xDW46ZTfA';
        $headers[] = 'Accept: */*';

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($nodecode) {
            return $output;
        } else {
            $data = json_decode($output, TRUE);
            return $data;
        }
    }

    function _result_out($data = [], $code = 0, $message = '')
    {

        if ($code > 0) {
            http_response_code(400);
        }

        $_POST = json_decode(file_get_contents('php://input'), TRUE);
        echo json_encode([
            'error_code' => $code,
            'message' => $message,
            'data' => $data,
            'post_data' => $_POST
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function login_old($phone)
    {
        $url = $this->url . 'persons?channel=' . $phone;

        $data = $this->_request($url);

        if (count($data) == 0) {
            $this->_result_out([], 1);
        }

        if (count($data) > 0) {
            $sms_code = rand(1111, 9999);

            if ($phone == '380980971283') {
                $sms_code = 1111;
            }

            //$sms_code = 1111;
            foreach ($data as $user) {

                $check_user = $this->db->get_where('users', ['patientId' => $user['id']])->row_array();


                if ($check_user) {
                    $this->db->where('id', $check_user['id'])->update('users', [
                        'smsCode' => $sms_code
                    ]);
                } else {

                    $this->db->insert('users', [
                        'firstName' => $user['firstName'],
                        'lastName' => $user['lastName'],
                        'patronymicName' => $user['patronymicName'],
                        'phone' => $phone,
                        'patientID' => $user['id'],
                        'smsCode' => $sms_code
                    ]);
                }
            }

            $this->_sendsms($phone, $sms_code);
        }

        $this->_result_out();

    }

    public function sendcode_old($phone = '', $code = '')
    {
        if ($phone == '' || $code == '') {
            $this->_result_out([], 1, 'Fill all fields');
        }

        $users = $this->db->get_where('users', [
            'phone' => $phone,
            'smsCode' => $code
        ])->result_array();

        if (count($users) == 0) {
            $this->_result_out([], 2, 'User not found');
        }

        $users_result = [];

        foreach ($users as $user) {

            $user['token'] = md5($user['id'] . $user['phone'] . 'awsedrfsalttgyhujikolp');

            $this->db->where('id', $user['id'])->update('users', [
                'smsCode' => '',
                'token' => $user['token']
            ]);

            array_push($users_result, $this->db->get_where('users', ['id' => $user['id']])->row_array());

            $url = $this->url . 'persons/' . $user['patientID'] . '/documents';
            $documents = $this->_request($url);


            foreach ($documents as $document) {
                $check_doc = $this->db->get_where('notifications', ['document' => $document['id']])->row_array();

                if (empty($check_doc)) {
                    $this->db->insert('notifications', [
                        'userId' => $user['id'],
                        'header' => 'Новий документ',
                        'text' => $document['name'],
                        'date' => date('Y-m-d H:i:s'),
                        'document' => $document['id'],
                        'hidden' => 1
                    ]);
                }
            }

            $url = $this->url . 'employees?&unitId=66';
            $employees = $this->_request($url);

            $url = $this->url . 'appointments?patientId=' . $user['patientID'];
            $data = $this->_request($url);

            foreach ($data as $key => $item) {

                $url = $this->url . 'appointments/' . $item['id'];
                $appointmentData = $this->_request($url);

                // foreach ($employees as $employee) {
                //     foreach ($employee['executorEmployees'] as $exec) {
                //         if ($exec['resourceId'] == $appointmentData['mainExecutorId']) {
                //             $appointmentData['executor']['fullName'] = $employee['fullName'];
                //             $appointmentData['executor']['spec'] = $employee['specialities'][0]['name'];
                //         }
                //     }
                // }

                foreach ($employees as $employee) {
                    if ($employee['executorId'] == $appointmentData['mainExecutorId']) {
                        $appointmentData['executor']['fullName'] = $employee['fullName'];
                        $appointmentData['executor']['spec'] = $employee['specialities'][0]['name'];
                    }
                }

                $appointment = $this->db->get_where('appointments', ['appointmentID' => $item['id']])->row_array();

                $appointmentData['plannedStartTime'] = str_replace('T', ' ', $appointmentData['plannedStartTime']);

                if (empty($appointment)) {

                    $this->db->insert('appointments', [
                        'patientId' => $user['patientID'],
                        'appointmentID' => $item['id'],
                        'visitConfirmationState' => $appointmentData['visitConfirmationState'],
                        'state' => $appointmentData['state'],
                        'date' => $appointmentData['plannedStartTime'],
                        'time' => strtotime($appointmentData['plannedStartTime']),
                        'data' => json_encode($appointmentData)
                    ]);

                }
            }
        }

        $this->_result_out($users_result);


    }

    public function get_profile($token = '')
    {
        $userData = $this->db->get_where('users', ['token' => $token])->row_array();

        if (empty($userData)) {
            $this->_result_out([], 2, 'User not found');
        }

        $url = $this->url . 'clients?filters%5Bphone%5D=' . $userData['phone'];
        $data = $this->_request($url);
        $clients = $data['clients'];

        if (!empty($clients)) {
            $client = $clients[0];

            $clientDBId = $this->db->get_where('users', ['clientId' => $client['clientId']])->row_array();

            if (empty($clientDBId)) {
                $this->db->update('users', [
                    'clientId' => $client['clientId']
                ], ['phone' => $userData['phone']]);
            }
        }

        $user = $this->db->get_where('users', ['phone' => $userData['phone']])->row_array();
        $this->_result_out($user);
    }

    public function update_profile($token = '')
    {

        $_POST = json_decode(file_get_contents('php://input'), TRUE);

        if ($_SERVER['REDIRECT_REQUEST_METHOD'] == 'POST') {

            if (!empty($_POST)) {

                $this->db->update('users', [
                    'name' => $_POST['name'],
                    'middleName' => $_POST['middleName'],
                    'surname' => $_POST['surname'],
                ], ['token' => $token]);

                $userData = $this->db->get_where('users', ['token' => $token])->row_array();

                $this->send_comment($userData['phone'], 'Користувач оновив дані. Нове ПІБ - ' . $userData['surname'] . ' ' . $userData['name'] . ' ' . $userData['middleName'], $userData['clientId']);

                $this->_result_out([]);
            }
        }
    }

    public function login($phone)
    {

        $url = $this->url . 'clients?filters%5Bphone%5D=' . $phone;
        $data = $this->_request($url);
        $clients = $data['clients'];

        $phone = str_replace('%20', ' ', $phone);

        $clientDB = $this->db->get_where('users', ['phone' => $phone])->row_array();

        $sms_code = rand(1111, 9999);
        $sms_code = 1111;

        if (!empty($clients)) {
            $client = $clients[0];

            if (empty($clientDB)) {
                $this->db->insert('users', [
                    'clientId' => $client['clientId'],
                    'phone' => $phone,
                    'name' => $client['name'],
                    'middleName' => $client['middleName'],
                    'surname' => $client['surname'],
                    'smsCode' => $sms_code
                ]);
            } else {
                $this->db->update('users', [
                    'clientId' => $client['clientId'],
                    'phone' => $phone,
                    'name' => $client['name'],
                    'middleName' => $client['middleName'],
                    'surname' => $client['surname'],
                    'smsCode' => $sms_code
                ], ['phone' => $phone]);
            }
        } else {

            if (empty($clientDB)) {

                $this->db->insert('users', [
                    'phone' => $phone,
                    'smsCode' => $sms_code
                ]);

                $this->send_comment($phone, 'Новий користувач - ' . $phone . ' | FROM APP');

            } else {
                $this->db->update('users', [
                    'smsCode' => $sms_code
                ], ['id' => $clientDB['id']]);
            }

        }

        $this->_result_out([]);

    }

    public function sendcode($phone = '', $code = '')
    {
        $phone = str_replace('%20', ' ', $phone);

        if ($phone == '' || $code == '') {
            $this->_result_out([], 1, 'Fill all fields');
        }

        $user = $this->db->get_where('users', [
            'phone' => $phone,
            'smsCode' => $code
        ])->row_array();

        if (empty($user)) {
            $this->_result_out([], 2, 'User not found');
        }

        $token = md5($user['id'] . $user['phone'] . 'awsedrfsalttgyhujikolp');

        $this->db->where('id', $user['id'])->update('users', [
            'smsCode' => '',
            'token' => $token
        ]);

        $this->_result_out(['token' => $token]);


    }

    public function get_cars($token)
    {
        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        $url = $this->url . 'clients?filters%5Bids%5D=[' . $user['clientId'] . ']';
        $data = $this->_request($url);

        $vehicles = $data['clients'][0]['vehicles'];

        foreach ($vehicles as $key => $vehicle) {
            $url = $this->url . 'orders?vehicleId=' . $vehicle['id'];
            $data = $this->_request($url);
            $orders = $data;
            $vehicles[$key]['km'] = 0;

            if (isset($orders['vehicleData']['currentMileage'])) {
                $vehicles[$key]['km'] = $orders['vehicleData']['currentMileage'];
            }
        }

        foreach ($vehicles as $key => $vehicle) {
            $findCar = $this->db->where(['vin' => $vehicle['vin']])->get('cars')->row_array();

            if (empty($findCar)) {
                $this->db->insert('cars', [
                    'userId' => $user['id'],
                    'clientId' => $user['clientId'],
                    'carId' => $vehicle['id'],
                    'vin' => $vehicle['vin'],
                    'brand' => $vehicle['make'],
                    'model' => $vehicle['model'],
                    'year' => $vehicle['year'],
                    'number' => $vehicle['number'],
                    'modification' => $vehicle['modification'],
                    'type' => $vehicle['bodyType'],
                    'fuel' => $vehicle['fuelType'],
                    'km' => $vehicle['km'],
                    'volume' => $vehicle['capacity']
                ]);
            } else {
                $this->db->update('cars', [
                    'userId' => $user['id'],
                    'clientId' => $user['clientId'],
                    'carId' => $vehicle['id'],
                    'vin' => $vehicle['vin'],
                    'brand' => $vehicle['make'],
                    'model' => $vehicle['model'],
                    'year' => $vehicle['year'],
                    'number' => $vehicle['number'],
                    'modification' => $vehicle['modification'],
                    'type' => $vehicle['bodyType'],
                    'fuel' => $vehicle['fuelType'],
                    'km' => $vehicle['km'],
                    'volume' => $vehicle['capacity']
                ], ['id' => $findCar['id']]);
            }
        }

        $cars = $this->db->get_where('cars', ['userID' => $user['id']])->result_array();

        foreach ($cars as $key => $car) {
            if ($car['carId'] > 0) {
                $founded = false;

                foreach ($vehicles as $vehicle) {
                    if ($vehicle['id'] == $car['carId']) {
                        $founded = true;
                    }
                }

                if (!$founded) {
                    $this->db->delete('cars', ['id' => $car['id']]);
                }

            }
        }

        $cars = $this->db->get_where('cars', ['userID' => $user['id']])->result_array();
        $this->_result_out($cars);
    }

    public function get_cars_old($token)
    {
        $user = $this->db->get_where('users', ['token' => $token])->row_array();

        $url = $this->url . 'clients?filters%5Bids%5D=[' . $user['clientId'] . ']';
        $data = $this->_request($url);

        $vehicles = $data['clients'][0]['vehicles'];

        foreach ($vehicles as $key => $vehicle) {
            $url = $this->url . 'orders?vehicleId=' . $vehicle['id'];
            $data = $this->_request($url);
            $orders = $data;
            $vehicles[$key]['km'] = 0;

            if (isset($orders['vehicleData']['currentMileage'])) {
                $vehicles[$key]['km'] = $orders['vehicleData']['currentMileage'];
            }
        }

        $this->_result_out($vehicles);
    }

    public function get_car($token, $id)
    {

        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        $car = $this->db->get_where('cars', ['userId' => $user['id'], 'id' => $id])->row_array();

        if ($car['carId'] > 0) {
            $url = $this->url . 'orders?vehicleId=' . $car['carId'];
            $data = $this->_request($url);
            $orders = $data;


            $km = 0;
            if (isset($orders['vehicleData']['currentMileage'])) {
                $km = $orders['vehicleData']['currentMileage'];
            }

            $this->db->update('cars', ['km' => $km], ['id' => $car['id']]);
        }

        $car = $this->db->get_where('cars', ['userId' => $user['id'], 'id' => $id])->row_array();

        $car['orders'] = [];


        if ($car['carId'] > 0) {
            $url = $this->url . 'orders?vehicleId=' . $car['carId'];
            $data = $this->_request($url);
            $car['orders'] = $data['orders'];
        }

        $url = $this->url . 'clients?filters%5Bids%5D=[' . $user['clientId'] . ']';
        $data = $this->_request($url);

        $car['vehicle'] = [];

        foreach ($data['clients'][0]['vehicles'] as $vehicle) {
            if ($vehicle['id'] == $car['carId']) {
                $car['vehicle'] = $vehicle;
            }
        }

        $car['regulations'] = [];

        $url = $this->url . 'vehicle/regulations?vehicleId=' . $car['carId'] . '&onlyActive=true';
        $data = $this->_request($url);

        if (isset($data['result'])) {
            $car['regulations'] = $data['result'];
        }

        $this->_result_out($car);
    }

    public function get_car_old($token, $id)
    {

        $user = $this->db->get_where('users', ['token' => $token])->row_array();

        $url = $this->url . 'clients?filters%5Bids%5D=[' . $user['clientId'] . ']';
        $data = $this->_request($url);

        $vehicle = [];

        foreach ($data['clients'][0]['vehicles'] as $key => $car) {
            if ($car['id'] == $id) {
                $vehicle = $car;
            }
        }

        $url = $this->url . 'orders?vehicleId=' . $vehicle['id'];
        $data = $this->_request($url);
        $vehicle['orders'] = $data;


        $vehicle['km'] = 0;
        if (isset($vehicle['orders']['vehicleData']['currentMileage'])) {
            $vehicle['km'] = $vehicle['orders']['vehicleData']['currentMileage'];
        }


        $this->_result_out($vehicle);
    }

    public function delete_car($token, $id)
    {

        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        $car = $this->db->delete('cars', ['userId' => $user['id'], 'id' => $id]);

        $this->_result_out();
    }

    public function send_comment($phone, $text, $client_id = '')
    {
        $phone = str_replace('%20', ' ', $phone);

        $data = [
            'businessId' => 3453,
            'phone' => $phone,
            'date' => date('Y-m-d'),
            'time' => date('H:i', time() + 600),
            'comment' => $text
        ];

        if ($client_id != '') {
            $data['clientId'] = $client_id;
        }

        $this->_request($this->url . 'orders/by_third_party', 'POST', $data);
    }

    public function car_add()
    {
        $_POST = json_decode(file_get_contents('php://input'), TRUE);

        if ($_SERVER['REDIRECT_REQUEST_METHOD'] == 'POST') {
            if (!empty($_POST)) {

                $user = $this->db->get_where('users', ['token' => $_POST['token']])->row_array();
                if (empty($user))
                    $this->_result_out();

                $this->db->insert('cars', [
                    'userId' => $user['id'],
                    'vin' => $_POST['vin'],
                    'brand' => $_POST['brand'],
                    'model' => $_POST['model'],
                    'year' => $_POST['year'],
                    'number' => $_POST['number'],
                    'type' => $_POST['type'],
                    'fuel' => $_POST['fuel'],
                    'km' => $_POST['km'],
                    'volume' => $_POST['volume'],
                    'modification' => $_POST['modification']
                ]);

                $this->send_comment($user['phone'], 'Користувач ' . $user['surname'] . '' . $user['name'] . ' ' . $user['phone'] . ' додав авто - ' . ' ' . $_POST['brand'] . ' ' . $_POST['model'] . ' ' . $_POST['type'] . ' ' . $_POST['year'] . ', ' . $_POST['fuel'] . ', модифікація ' . $_POST['volume'] . ', держ.номер ' . $_POST['number'] . ', VIN ' . $_POST['vin'] . ', пробіг ' . $_POST['km'] . ' | FROM APP');

                $this->_result_out();
            }
        }
    }

    public function visits($token)
    {

        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        $url = 'https://api.carbook.pro/orders?client=' . $user['clientId'];
        $data = $this->_request($url);

        $this->_result_out($data['orders']);

    }

    public function visit($token, $id)
    {

        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        $url = 'https://api.carbook.pro/orders/' . $id;
        $data = $this->_request($url);

        $this->_result_out($data);
    }

    public function create_visit($token)
    {
        $_POST = json_decode(file_get_contents('php://input'), TRUE);

        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        if (empty($user))
            $this->_result_out();

        if ($_SERVER['REDIRECT_REQUEST_METHOD'] == 'POST') {

            $phone = str_replace('%20', ' ', $user['phone']);

            $data = [
                'businessId' => 3453,
                'clientId' => intval($user['clientId']),
                'clientVehicleId' => intval($_POST['car']),
                'phone' => $user['phone'],
                'date' => $_POST['date'],
                'time' => $_POST['time'],
                'comment' => $_POST['comment']
            ];

            if ($data['clientVehicleId'] == "") {
                unset($data['clientVehicleId']);
            }

            if ($data['comment'] == "") {
                unset($data['comment']);
            }


            $data = $this->_request($this->url . 'orders/by_third_party', 'POST', $data);
        }

        $this->_result_out($data);
    }

    public function get_notifications($token)
    {
        $user = $this->db->get_where('users', ['token' => $token])->row_array();
        $notifications = $this->db->get_where('notifications', ['clientId' => $user['clientId']])->result_array();
        $this->_result_out(array_reverse($notifications));
    }

    public function cron()
    {
        $url = 'https://api.carbook.pro/orders';
        $data = $this->_request($url);

        print_r($data['orders']);

        foreach ($data['orders'] as $visit) {
            $check = $this->db->get_where('visits', [
                'visitId' => $visit['id']
            ])->row_array();

            if (empty($check)) {
                $this->db->insert('visits', [
                    'visitId' => $visit['id'],
                    'status' => $visit['status']
                ]);
            } else {
                if ($check['status'] != $visit['status']) {
                    $this->db->update('visits', ['status' => $visit['status']], ['visitId' => $visit['id']]);
                    $this->db->insert('notifications', [
                        'clientId' => $visit['clientId'],
                        'text' => "Статус візиту оновлено"
                    ]);
                }
            }
        }

    }

    public function check()
    {
        $url = 'https://api.carbook.pro/orders';
        $data = $this->_request($url);
        print_r($data);
    }

    public function get_doc($type = '', $id = 1297977)
    {

        $url = 'https://api.carbook.pro/orders/reports/' . $type . '/' . $id;
        $data = $this->_request($url, 'GET', [], true);
        echo $data;
    }

    public function get_document($type = '', $id = 1297977)
    {
        
        $file_type = '';

        switch($type){
            case 'diagnosticsReport': $file_type = 'diagnostics'; break;
            case 'completedWorkReport': $file_type = 'completed_work'; break;
            case 'invoiceReport': $file_type = 'invoice'; break;
        }

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment;filename=".$file_type."-RD-3453-" . $id . ".pdf");

        $url = 'https://api.carbook.pro/orders/reports/' . $type . '/' . $id;
        $data = $this->_request($url, 'GET', [], true);

        echo $data;
    }

}


/*

date: "2023-07-22"
duration: 30
executorId: 1298
services: [{routineId: 117, unitPrice: 500, discount: 0}]
tg: {doctorName: "Килинник Ірина Сергіївна", routine: "Консультація гінеколога повторна",…}
time: "09:00"

*/