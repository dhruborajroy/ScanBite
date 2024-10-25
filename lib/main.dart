import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'package:get_storage/get_storage.dart';
import 'package:flutter/cupertino.dart'; // Import Cupertino for the date picker

void main() async {
  await GetStorage.init();
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
  final MobileScannerController _cameraController = MobileScannerController();
  bool _isCameraActive = false;
  String scannedData = '';
  String selectedMealType = 'breakfast';

  bool isSuccess = false;
  String name = '';
  String hallId = '';
  String mealType = '';
  String errorMessage = '';
  DateTime selectedDate = DateTime.now();
  bool isProcessing = false; // Add this flag to prevent multiple requests

  final TextEditingController _urlController = TextEditingController(); // Controller for URL input

  // Function to handle barcode detection
  void _onBarcodeDetected(BarcodeCapture barcodeCapture) async {
    if (isProcessing) return; // Prevent multiple requests

    final Barcode barcode = barcodeCapture.barcodes.first;
    if (barcode.rawValue != null) {
      scannedData = barcode.rawValue!;
      if (kDebugMode) {
        print("QR Code: $scannedData");
      }

      _cameraController.stop();
      setState(() {
        _isCameraActive = false;
        isProcessing = true; // Mark processing as true before the request
      });

      try {
        // Decode the scanned data
        Map<String, dynamic> qrData = json.decode(scannedData);

        // Send the scanned data
        await _sendScannedData(qrData);

      } catch (e) {
        // Handle error during decoding
        if (kDebugMode) {
          print("Error decoding scanned data: $e");
        }
        setState(() {
          isSuccess = false;
          errorMessage = 'Invalid QR code data, could not register for meal.';
        });
      } finally {
        setState(() {
          isProcessing = false;
        });
      }
    }
  }

  Future<void> _sendScannedData(Map<String, dynamic> qrData) async {
    var headers = {
      'Content-Type': 'application/json'
    };

    // Check if user has entered a URL or use default
    String apiUrl = _urlController.text.isNotEmpty
        ? _urlController.text
        : 'https://mashallah.shop/app/api.php';

    var request = http.Request('POST', Uri.parse(apiUrl));

    request.body = json.encode({
      "app_id": qrData["app_id"] ?? "default_app_id",
      "name": qrData["name"] ?? "default_name",
      "hall_id": qrData["hall_id"] ?? "default_hall_id",
      "reg_no":qrData["reg_no"] ?? "default_reg_no",
      "date": selectedDate.toIso8601String(),
      "meal_type": selectedMealType
    });

    request.headers.addAll(headers);

    http.StreamedResponse response = await request.send();

    if (response.statusCode == 200) {
      String responseBody = await response.stream.bytesToString();

      if (kDebugMode) {
        print("Full response::: $responseBody");
      }

      Map<String, dynamic> responseData = json.decode(responseBody);

      if (responseData["status_code"] == "200") {
        // Success
        setState(() {
          isSuccess = true;
          name = responseData["name"] ?? "default_name";
          hallId = qrData["hall_id"] ?? "default_hall_id";
          mealType = selectedMealType;
          errorMessage = responseData["message"] ?? 'Did not register for meal';
        });
      } else {
        // Failure
        setState(() {
          isSuccess = false;
          errorMessage = responseData["message"] ?? 'Did not register for meal';
        });
      }
    } else {
      // HTTP error
      setState(() {
        isSuccess = false;
        errorMessage = 'Did not register for meal';
      });

      if (kDebugMode) {
        print("HTTP error: ${response.statusCode}");
      }
    }
  }

  // Date Picker function
  void _showDatePicker(BuildContext context) {
    showCupertinoModalPopup(
      context: context,
      builder: (BuildContext builder) {
        return Container(
          height: 250,
          color: Colors.white,
          child: Column(
            children: [
              SizedBox(
                height: 200,
                child: CupertinoDatePicker(
                  mode: CupertinoDatePickerMode.date,
                  initialDateTime: selectedDate,
                  onDateTimeChanged: (DateTime newDate) {
                    setState(() {
                      selectedDate = newDate;
                    });
                  },
                ),
              ),
              CupertinoButton(
                child: const Text('Done'),
                onPressed: () {
                  Navigator.of(context).pop();
                },
              )
            ],
          ),
        );
      },
    );
  }

  void _toggleCamera() {
    if (_isCameraActive) {
      _cameraController.stop();
      setState(() {
        _isCameraActive = false;
      });
    } else {
      setState(() {
        _isCameraActive = true;
      });
      _cameraController.start();
    }
  }

  @override
  void dispose() {
    _cameraController.dispose();
    _urlController.dispose(); // Dispose the URL controller
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
              if (_isCameraActive)
                SizedBox(
                  height: 300,
                  width: 300,
                  child: MobileScanner(
                    controller: _cameraController,
                    onDetect: _onBarcodeDetected,
                  ),
                ),
              ElevatedButton(
                onPressed: _toggleCamera,
                child: Text(_isCameraActive ? 'Stop Scan' : 'Scan New QR Code'),
              ),
              const SizedBox(height: 20),

              // URL input field
              TextFormField(
                controller: _urlController,
                decoration: const InputDecoration(
                  labelText: 'Enter API URL (optional)',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 20),

              if (isSuccess)
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
                    Text(
                      'Registration No : $errorMessage',
                      style: const TextStyle(
                        color: Colors.green,
                        fontSize: 16,
                      ),
                    ),
                  ],
                )
              else if (errorMessage.isNotEmpty)
                Text(
                  errorMessage,
                  style: const TextStyle(
                    color: Colors.red,
                    fontSize: 16,
                  ),
                ),
              const SizedBox(height: 20),
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
                title: const Text('Lunch'),
                leading: Radio<String>(
                  value: 'lunch',
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

              // Date picker button
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Selected Date:',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  ElevatedButton(
                    onPressed: () => _showDatePicker(context),
                    child: const Text('Pick Date'),
                  ),
                ],
              ),
              Text(
                '${selectedDate.toLocal()}'.split(' ')[0],
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 20),
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
