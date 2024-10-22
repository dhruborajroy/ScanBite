import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ScanBite - Meal Status App',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: MealStatusPage(),
    );
  }
}

class MealStatusPage extends StatefulWidget {
  const MealStatusPage({super.key});

  @override
  MealStatusPageState createState() => MealStatusPageState();
}

class MealStatusPageState extends State<MealStatusPage> {
  DateTime? selectedDate;
  String statusMessage = "";
  Color badgeColor = Colors.transparent;
  bool showBadge = false;
  String? serverIp;
  TextEditingController ipController = TextEditingController();
  bool hasMeal = false; // To track meal status selection

  @override
  void initState() {
    super.initState();
    _loadServerIp();
  }

  Future<void> _loadServerIp() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    setState(() {
      serverIp = prefs.getString('server_ip') ?? '';
      ipController.text = serverIp ?? '';
    });
  }

  Future<void> _saveServerIp(String ip) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setString('server_ip', ip);
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
    );
    if (picked != null && picked != selectedDate) {
      setState(() {
        selectedDate = picked;
      });
    }
  }

  Future<void> simulateQrScan(String hallId) async {
    String date = "${selectedDate?.year}-${selectedDate?.month}-${selectedDate?.day}";
    String mealStatus = hasMeal ? "1" : "0"; // New: Send meal status

    if (serverIp != null && serverIp!.isNotEmpty) {
      await checkAndUpdateMealStatus(hallId, date, mealStatus);
    } else {
      setState(() {
        showBadge = true;
        badgeColor = Colors.red;
        statusMessage = "Please enter a valid server IP.";
      });
    }
  }

  Future<void> checkAndUpdateMealStatus(String hallId, String date, String mealStatus) async {
    String serverUrl;
    if (serverIp != null && (serverIp!.startsWith('http://') || serverIp!.startsWith('https://'))) {
      serverUrl = serverIp!;
    } else {
      serverUrl = 'http://$serverIp';
    }

    var url = Uri.parse('$serverUrl/check_and_update_meal_status.php');
    var response = await http.post(
      url,
      headers: {"Content-Type": "application/json"},
      body: jsonEncode({"hall_id": hallId, "date": date, "meal_status": mealStatus}),
    );

    if (response.statusCode == 200) {
      var jsonResponse = jsonDecode(response.body);
      if (jsonResponse['status'] == 'success') {
        setState(() {
          showBadge = true;
          badgeColor = hasMeal ? Colors.green : Colors.red;
          statusMessage = hasMeal ? "Meal marked as ON" : "Meal marked as OFF";
        });
      } else {
        setState(() {
          showBadge = true;
          badgeColor = Colors.grey;
          statusMessage = "Failed to update meal status.";
        });
      }
    } else {
      setState(() {
        showBadge = true;
        badgeColor = Colors.red;
        statusMessage = "Server error. Failed to update status.";
      });
    }
  }

  // This method will be called when a barcode is detected
  void _onBarcodeDetected(BarcodeCapture barcodeCapture) {
    final Barcode barcode = barcodeCapture.barcodes.first;
    if (barcode.rawValue != null) {
      String hallId = barcode.rawValue!;
      simulateQrScan(hallId); // Pass the hallId scanned
    }
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
            children: <Widget>[
              TextField(
                controller: ipController,
                decoration: const InputDecoration(
                  labelText: 'Enter Server IP Address',
                  border: OutlineInputBorder(),
                ),
                onChanged: (value) {
                  setState(() {
                    serverIp = value;
                  });
                },
              ),
              const SizedBox(height: 10),
              ElevatedButton(
                onPressed: () {
                  _saveServerIp(serverIp!);
                },
                child: const Text("Save IP Address"),
              ),
              const SizedBox(height: 20),

              const Text(
                "Select a date:",
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              SizedBox(height: 10),
              ElevatedButton(
                onPressed: () => _selectDate(context),
                child: Text(selectedDate != null
                    ? "${selectedDate?.day}-${selectedDate?.month}-${selectedDate?.year}"
                    : "Pick a Date"),
              ),
              const SizedBox(height: 20),

              // Toggle for meal status
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    "Mark meal as ON",
                    style: TextStyle(fontSize: 16),
                  ),
                  Switch(
                    value: hasMeal,
                    onChanged: (value) {
                      setState(() {
                        hasMeal = value;
                      });
                    },
                  ),
                ],
              ),

              // MobileScanner widget for barcode scanning
              SizedBox(
                height: 300,
                width: 300,
                child: MobileScanner(
                  onDetect: _onBarcodeDetected, // Correct callback parameter
                ),
              ),

              const SizedBox(height: 40),

              if (showBadge)
                Container(
                  decoration: BoxDecoration(
                    color: badgeColor,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 24),
                  child: Text(
                    statusMessage,
                    style: const TextStyle(
                      fontSize: 18,
                      color: Colors.white,
                    ),
                  ),
                ),

              const SizedBox(height: 50),

              // Developer Section
              const Text(
                'Developer Info',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const CircleAvatar(
                radius: 50,
                backgroundImage: AssetImage('assets/images/developer_photo.png'),
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
