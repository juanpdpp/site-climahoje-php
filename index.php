<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css">
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function() {
            if (performance.navigation.type === 1) {
                window.location.href = 'index.php';
            }
        };
    </script>
    <?php
    include 'appidKey.php';

    $qualidade = '';
    $gas = [];
    $errorMessage = ''; 
    $nomeCidade = '';
    $lat = $lon = null; 
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (empty($_POST['buscar'])) { 
            $errorMessage = "Por favor, digite o nome de uma cidade!";
        } else {
            $name = str_replace(" ", "_", htmlspecialchars($_POST['buscar']));
    
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "http://api.openweathermap.org/geo/1.0/direct?q=".$name. "&limit=1&appid=" . $apiKey,
                CURLOPT_RETURNTRANSFER => true
            ]);
    
            $response = curl_exec($curl);
            curl_close($curl);
            $cidades = json_decode($response, true);
    
            if (!empty($cidades)) {
                foreach ($cidades as $cidade) {
                    $lat = $cidade['lat'];
                    $lon = $cidade['lon'];
                    $nomeCidade = $cidade['name'];
    
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "http://api.openweathermap.org/data/2.5/air_pollution?lat=" .$lat. "&lon=" .$lon. "&appid=" . $apiKey,
                        CURLOPT_RETURNTRANSFER => true
                    ]);
    
                    $airResponse = curl_exec($curl);
                    curl_close($curl);
    
                    $air = json_decode($airResponse, true);
    
                    if (!empty($air['list'])) {
                        $qualidadeAr = $air['list'][0]['main']['aqi'];
                        $gas = $air['list'][0]['components'];
    
                        switch($qualidadeAr) {
                            case 1:
                                $qualidade = "Muito Bom";
                                $class = "muito-bom";
                                break;
                            case 2:
                                $qualidade = "Bom";
                                $class = "bom";
                                break;
                            case 3:
                                $qualidade = "Moderada";
                                $class = "moderada";
                                break;
                            case 4:
                                $qualidade = "Ruim";
                                $class = "ruim";
                                break;
                            case 5:
                                $qualidade = "Horrível";
                                $class = "horrivel";
                                break;
                        }
                    } else {
                        $errorMessage = "Não consegui encontrar a qualidade do ar!";
                    }
                }
            } else {
                $errorMessage = "Não encontrei nenhuma cidade!";
            }
        }
    }
    ?>
</head>
<body>
    <title>Cidade</title>
    <div class="content-container">
        <header>
            <h1 class="header-title">Pesquise aqui a cidade que deseja verificar a poluição do ar</h1>
        </header>
        <div style="width: 100%;">
            <div class="form-container">
                <form method="POST">
                    <input type="text" name="buscar" id="buscar" class="input-text" placeholder="Digite nome da cidade">
                    <button type="submit" class="submit-button">Buscar</button>
                </form>
                <br>
                <?php if ($errorMessage): ?>
                    <div class="error-message"><?= $errorMessage ?></div>
                <?php endif; ?>
            </div>
            <main>
            <div style="display: flex; width: 100%;">
                <div class="tabelaPagina">
                    <?php if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_POST['buscar']) && !empty($cidades) && empty($errorMessage)) :?>
                    <div class="main-content">
                        <div style="width: 60%; padding: 20px;">
                            <h1 class="city-title">CIDADE: <?= $nomeCidade ?></h1>
                            <h2 class="air-quality">Índice de qualidade do ar: <?= $qualidade ?></h2>
                            <h3>Componentes de poluição:</h3>
                            <div class="tabelaCentralizar">
                            <table class="table-container">
                                <thead>
                                    <tr class="table-header">
                                        <th class="table-header-cell <?= $class ?>">PM2.5</th>
                                        <th class="table-header-cell <?= $class ?>">PM10</th>
                                        <th class="table-header-cell <?= $class ?>">NO2</th>
                                        <th class="table-header-cell <?= $class ?>">SO2</th>
                                        <th class="table-header-cell <?= $class ?>">O3</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-row">
                                        <td class="table-cell"><?= $gas['pm2_5'] ?? 'N/A' ?></td>
                                        <td class="table-cell"><?= $gas['pm10'] ?? 'N/A' ?></td>
                                        <td class="table-cell"><?= $gas['no2'] ?? 'N/A' ?></td>
                                        <td class="table-cell"><?= $gas['so2'] ?? 'N/A' ?></td>
                                        <td class="table-cell"><?= $gas['o3'] ?? 'N/A' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            <br>
                            <div class="tabelaCentralizar">
                                <div class="legenda">
                                    <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square-fill muitoBomLegenda" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                    Muito bom
                                    </div>
                                    <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square-fill bomLegenda" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                    Bom
                                    </div>
                                    <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square-fill moderadaLegenda" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                    Moderada
                                    </div>
                                    <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square-fill ruimLegenda" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                    Ruim
                                    </div>
                                    <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-square-fill horrivelLegenda" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2z"/>
                                    </svg>
                                    Horrível
                                    </div>
                                </div>
                            </div>
                            <div id="lat" style="display: none;"><?= $lat ?></div>
                            <div id="lon" style="display: none;"><?= $lon ?></div>
                        </div>
                        <div class="mapaStyle">
                            <div class="mapaPagina">
                                <div id="map"></div>
                            </div>
                            <?php endif; ?>
                        <div>
                    </div>
                <div>
            <div>
            </main>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var lat = parseFloat(document.getElementById('lat').textContent);
        var lon = parseFloat(document.getElementById('lon').textContent);

        if (!isNaN(lat) && !isNaN(lon)) {
            var mapa = L.map('map').setView([lat, lon], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(mapa);

            L.marker([lat, lon]).addTo(mapa)
                .bindPopup('<b>' + 'Cidade: ' + '<?= $nomeCidade ?>' + '</b><br>' + 'Índice de qualidade do ar: ' + '<?= $qualidade ?>')
                .openPopup();
        } else {
            console.log("Erro ao carregar as coordenadas.");
        }
    });
</script>
</html>