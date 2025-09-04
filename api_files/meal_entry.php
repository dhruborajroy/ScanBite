<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Meal Data Entry</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .card-container {
    background: #ffffff;
    padding: 40px 50px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 600px;
    text-align: center;
  }
  #loading {
    display: none;
    margin-top: 30px;
  }
  #loading img {
    width: 200px;
  }
  #result {
    margin-top: 30px;
  }
</style>
</head>
<body>

<div class="card-container">
    <h2 class="mb-3">Meal Data Entry</h2>
    <p class="mb-4 text-muted">Click the button below to start importing meal data. This may take a few moments.</p>

    <button id="startBtn" class="btn btn-primary btn-lg">Start Import</button>

    <div id="loading">
        <img src="https://i.gifer.com/YCZH.gif" alt="Processing...">
        <div class="mt-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 fw-bold">Processing your data, please wait...</p>
        </div>
    </div>

    <div id="result"></div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$('#startBtn').click(function() {
    $('#startBtn').hide();
    $('#loading').show();
    $('#result').html('');

    $.ajax({
        url: 'meal_data_entry.php', // backend script
        type: 'GET',
        success: function(response){
            $('#loading').hide();
            $('#result').html('<div class="alert alert-success">Process completed successfully!<br>' + response + '</div>');
        },
        error: function(xhr){
            $('#loading').hide();
            $('#result').html('<div class="alert alert-danger">An error occurred: ' + xhr.statusText + '</div>');
        }
    });
});
</script>

</body>
</html>
