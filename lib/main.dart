import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'package:get_storage/get_storage.dart';  // Import GetStorage

void main() async {
  await GetStorage.init();  // Initialize GetStorage
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'ScanBite - Meal Status App',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: const MealStatusPage(),
    );
  }
}

class MealStatusPage extends StatefulWidget {
  const MealStatusPage({super.key});

  @override
  MealStatusPageState createState() => MealStatusPageState();
}

class MealStatusPageState extends State<MealStatusPage> {
  final MobileScannerController _cameraController = MobileScannerController(); // MobileScanner controller
  bool _isCameraActive = false; // Control camera state
  String scannedData = ''; // Variable to store scanned QR code data
  String selectedMealType = 'breakfast'; // Variable to store selected meal type

  // Variables to store API response data
  bool isSuccess = false; // Track API call success
  String name = '';
  String hallId = '';
  String mealType = '';
  String errorMessage = ''; // Error message if the API call fails

  void _onBarcodeDetected(BarcodeCapture barcodeCapture) async {
    final Barcode barcode = barcodeCapture.barcodes.first;
    if (barcode.rawValue != null) {
      scannedData = barcode.rawValue!;
      if (kDebugMode) {
        print("QR Code: $scannedData");
      }

      // Turn off the camera after detecting the QR code
      _cameraController.stop();
      setState(() {
        _isCameraActive = false; // Update state to stop camera
      });

      // Parse the scanned data (assuming it's JSON)
      Map<String, dynamic> qrData = json.decode(scannedData);

      // Send the scanned data to the API
      await _sendScannedData(qrData);
    }
  }

  // Function to send scanned data to the API
  Future<void> _sendScannedData(Map<String, dynamic> qrData) async {
    var headers = {
      'Content-Type': 'application/json'
    };

    var request = http.Request('POST', Uri.parse('http://192.168.0.150/app/api.php'));

    // Dynamically set the body using the scanned QR code data and selected meal type
    request.body = json.encode({
      "app_id": qrData["app_id"] ?? "default_app_id",
      "name": qrData["name"] ?? "default_name",
      "hall_id": qrData["hall_id"] ?? "default_hall_id",
      "date": DateTime.now().toString(),
      "meal_type": selectedMealType // Pass the selected meal type
    });

    request.headers.addAll(headers);

    http.StreamedResponse response = await request.send();

    if (response.statusCode == 200) {
      String responseBody = await response.stream.bytesToString();
      Map<String, dynamic> responseData = json.decode(responseBody);

      // Update UI with the scanned information
      setState(() {
        isSuccess = true; // Successful API call
        name = responseData["name"] ?? "Unknown Name";
        hallId = responseData["hall_id"] ?? "Unknown Hall ID";
        mealType = selectedMealType;
        errorMessage = ''; // Clear any previous errors
      });
    } else {
      setState(() {
        isSuccess = false; // API call failed
        errorMessage = 'Did not register for meal';
      });
    }
  }

  // Function to toggle camera scanning
  void _toggleCamera() {
    if (_isCameraActive) {
      // Turn off the camera
      _cameraController.stop();
      setState(() {
        _isCameraActive = false;
      });
    } else {
      // Turn on the camera
      setState(() {
        _isCameraActive = true;
      });
      _cameraController.start();
    }
  }

  @override
  void dispose() {
    _cameraController.dispose(); // Dispose controller when the widget is removed
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('ScanBite - Meal Status Checker'),
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: <Widget>[
              // Show camera if activated
              if (_isCameraActive)
                SizedBox(
                  height: 300,
                  width: 300,
                  child: MobileScanner(
                    controller: _cameraController,
                    onDetect: _onBarcodeDetected, // Correct callback parameter
                  ),
                ),

              // Button to scan new QR code or stop scan
              ElevatedButton(
                onPressed: _toggleCamera, // Toggle camera on/off
                child: Text(_isCameraActive ? 'Stop Scan' : 'Scan New QR Code'),
              ),

              const SizedBox(height: 20),

              // Show the result based on the API response
              if (isSuccess)
              // Success message with name, hall ID, and meal type
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Name: $name',
                      style: const TextStyle(
                        color: Colors.green,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Hall ID: $hallId',
                      style: const TextStyle(
                        color: Colors.green,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Meal Type: $mealType',
                      style: const TextStyle(
                        color: Colors.green,
                        fontSize: 16,
                      ),
                    ),
                  ],
                )
              else if (errorMessage.isNotEmpty)
              // Error message if registration for meal failed
                Text(
                  errorMessage,
                  style: const TextStyle(
                    color: Colors.red,
                    fontSize: 16,
                  ),
                ),

              const SizedBox(height: 20),

              // Radio buttons for selecting meal type
              const Text(
                'Select Meal Type:',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              ListTile(
                title: const Text('Breakfast'),
                leading: Radio<String>(
                  value: 'breakfast',
                  groupValue: selectedMealType,
                  onChanged: (String? value) {
                    setState(() {
                      selectedMealType = value!;
                    });
                  },
                ),
              ),
              ListTile(
                title: const Text('Dinner'),
                leading: Radio<String>(
                  value: 'dinner',
                  groupValue: selectedMealType,
                  onChanged: (String? value) {
                    setState(() {
                      selectedMealType = value!;
                    });
                  },
                ),
              ),

              const SizedBox(height: 20),

              // Developer Section
              const Text(
                'Developer Info',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const CircleAvatar(
                radius: 50,
                backgroundImage: AssetImage('assets/images/joy.jpg'),
              ),
              const SizedBox(height: 10),
              const Text('Dhrubo Raj Roy'),
              const Text('dhruborajroy3@gmail.com'),
              const Text('01705927257'),
            ],
          ),
        ),
      ),
    );
  }
}


