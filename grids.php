<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grid Calculation Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px;
            max-width: 600px;
            margin: auto;
        }
        .grid-option {
            cursor: pointer;
            margin: 0 5px;
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #e9ecef;
            color: #495057;
            transition: background-color 0.3s, color 0.3s;
            font-size: 0.9rem;
        }
        .grid-option:hover {
            background-color: #007bff;
            color: white;
        }
        .grid-option.active {
            background-color: #007bff;
            color: white;
        }
        .input-cell {
            width: 100px;
            height: 30px;
            text-align: center;
            font-size: 0.8rem;
            background-color: #f1f3f5;
        }
        .table-container {
            overflow-x: auto;
        }
        .table {
            font-size: 0.8rem;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #e9ecef;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004494;
        }
    </style>
</head>
<body>
<h2 class="text-center">Grid Calculation Table</h2>
<div class="container">
    <?php
    $gridLength = 3; 
    $customerInput = '---'; 
    $selectedGrid = '111';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $customerInput = '';
        for ($i = 0; $i < $gridLength; $i++) {
            $customerInput .= isset($_POST["digit$i"]) ? $_POST["digit$i"] : '0';
        }
        if (isset($_POST['selectedGrid'])) {
            $selectedGrid = $_POST['selectedGrid'];
        }
    }
    ?>

    <form id="gridForm" method="post" action="">
        <input type="hidden" name="selectedGrid" id="selectedGrid" value="<?php echo htmlspecialchars($selectedGrid); ?>">
        <div class="mb-4">
            <div class="d-flex flex-wrap mb-1 justify-content-between align-items-center">
                <div id="gridOptionsContainer" class="d-flex flex-wrap"></div>
                <button type="submit" class="btn btn-primary">Calculate</button>
            </div>
            <div class="table-container">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Step</th>
                            <?php for ($i = 0; $i < $gridLength; $i++): ?>
                                <th>Digit <?php echo $i + 1; ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Input</td>
                            <?php for ($i = 0; $i < $gridLength; $i++): ?>
                                <td>
                                    <input type="number" class="form-control input-cell" name="digit<?php echo $i; ?>" value="<?php echo isset($_POST["digit$i"]) ? htmlspecialchars($_POST["digit$i"]) : ''; ?>" min="0" max="9" required>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
    <div id="gridTableContainer" class=" table-container"></div>
    <script>
        let modulus = 10;
        let gridValues = {};

        fetch('formula_values.json')
            .then(response => response.json())
            .then(data => {
                modulus = data.modulus;
                gridValues = data.gridValues;
                updateGridOptions();
                showGrid('<?php echo htmlspecialchars($selectedGrid); ?>', <?php echo isset($_POST['selectedGrid']) ? 'false' : 'true'; ?>); // Show default placeholder if not calculated
            })
            .catch(error => console.error('Error fetching formula values:', error));

        function updateGridOptions() {
            const gridOptionsContainer = document.getElementById('gridOptionsContainer');
            gridOptionsContainer.innerHTML = '';
            for (let key in gridValues) {
                if (gridValues.hasOwnProperty(key)) {
                    gridOptionsContainer.innerHTML += `<div class="grid-option" onclick="showGrid('${key}')">${gridValues[key]} Grid</div>`;
                }
            }
        }

        function showGrid(gridValue, isDefault = false) {
            document.getElementById('selectedGrid').value = gridValue;
            const gridTableContainer = document.getElementById('gridTableContainer');
            gridTableContainer.innerHTML = generateTable(gridValue, isDefault ? '---' : '<?php echo htmlspecialchars($customerInput); ?>');

            document.querySelectorAll('.grid-option').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.grid-option[onclick="showGrid('${gridValue}')"]`).classList.add('active');
        }

        function generateTable(gridval, num) {
            let numArr = num === '---' ? Array(gridval.length).fill('---') : num.split('');
            let gridarr = gridval.split('');

            let table = '<table class="table table-bordered"><thead><tr><th>Step</th>';
            for (let i = 0; i < gridarr.length; i++) {
                table += '<th>Digit ' + (i + 1) + '</th>';
            }
            table += '</tr></thead><tbody>';

            table += '<tr><td>Grid</td>';
            for (let value of gridarr) {
                table += '<td>' + value + '</td>';
            }
            table += '</tr>';

            table += '<tr><td>Original</td>';
            for (let value of numArr) {
                table += '<td>' + (value === '---' ? '---' : value) + '</td>';
            }
            table += '</tr>';
            step = 1;

            if (num !== '---') {
                let originalNum = num;
                let newNum;
                do {
                    newNum = [];
                    for (let i = 0; i < numArr.length; i++) {
                        let sum = parseInt(numArr[i]) + parseInt(gridarr[i]);
                        newNum[i] = sum % modulus; 
                    }
                    table += '<tr><td>Step ' + step + '</td>';
                    for (let value of newNum) {
                        table += '<td>' + value + '</td>';
                    }
                    table += '</tr>';

                    numArr = newNum.slice();
                    step++;
                } while (newNum.join('') !== originalNum);
            } else {
                table += '<tr><td>Step 1</td>';
                for (let i = 0; i < gridarr.length; i++) {
                    table += '<td>---</td>';
                }
                table += '</tr>';
            }

            table += '</tbody></table>';
            return table;
        }

        document.addEventListener('DOMContentLoaded', function() {
            showGrid('<?php echo htmlspecialchars($selectedGrid); ?>', <?php echo isset($_POST['selectedGrid']) ? 'false' : 'true'; ?>); // Initialize with placeholders if not calculated
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
