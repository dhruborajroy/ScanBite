import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
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
  @override
  _MealStatusPageState createState() => _MealStatusPageState();
}

class _MealStatusPageState extends State<MealStatusPage> {
  DateTime? selectedDate;
  String statusMessage = "";
  Color badgeColor = Colors.transparent;
  bool showBadge = false;
  String? serverIp;
  TextEditingController ipController = TextEditingController();
  bool hasMeal = false; // Added: To track meal status selection

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

  Future<void> simulateQrScan() async {
    String hallId = "200130"; // Simulated hall_id from QR code scan
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('ScanBite - Meal Status Checker'),
      ),
      body: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: <Widget>[
              TextField(
                controller: ipController,
                decoration: InputDecoration(
                  labelText: 'Enter Server IP Address',
                  border: OutlineInputBorder(),
                ),
                onChanged: (value) {
                  setState(() {
                    serverIp = value;
                  });
                },
              ),
              SizedBox(height: 10),
              ElevatedButton(
                onPressed: () {
                  _saveServerIp(serverIp!);
                },
                child: Text("Save IP Address"),
              ),
              SizedBox(height: 20),

              Text(
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
              SizedBox(height: 20),

              // Toggle for meal status
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
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

              ElevatedButton(
                onPressed: simulateQrScan,
                child: Text("Scan QR Code"),
              ),
              SizedBox(height: 40),

              if (showBadge)
                Container(
                  decoration: BoxDecoration(
                    color: badgeColor,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  padding: EdgeInsets.symmetric(vertical: 12, horizontal: 24),
                  child: Text(
                    statusMessage,
                    style: TextStyle(
                      fontSize: 18,
                      color: Colors.white,
                    ),
                  ),
                ),

              SizedBox(height: 50),

              // Developer Section
              Text(
                'Developer Info',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              CircleAvatar(
                radius: 50,
                backgroundImage: AssetImage('assets/images/developer_photo.png'),
              ),
              SizedBox(height: 10),
              Text('Dhrubo Raj Roy'),
              Text('dhruborajroy3@gmail.com'),
              Text('01705927257'),
            ],
          ),
        ),
      ),
    );
  }
}
