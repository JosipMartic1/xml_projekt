<?php
//pokretanje sesije
session_start();

//iniciranje brojaca na nulu
$_SESSION["correct"] ??= 0;
$_SESSION["incorrect"] ??= 0;

//reset brojaca kada se stisnee gumb
if (($_POST["submit"] ?? "") === "reset") {
    $_SESSION["correct"] = $_SESSION["incorrect"] = 0;
    unset($_SESSION["current_question"], $_SESSION["current_answers"]);
}

//ucitavanje xml baze pitanja
if (!isset($_SESSION["current_question"])) {
    $xml = simplexml_load_file("questions.xml");
    $questions = [];
    foreach ($xml->question as $q) {
        $questions[] = $q;
    }

//biranje pitanja iz niza, izvlacenje teksta i id-a  
    $question = $questions[array_rand($questions)];
    $questions_text = (string)$question->text;
    $questions_id = (string)$question["id"];

    $answers = [];
    foreach ($question->answer as $answer) {
        $answers[] = [
            "text" => (string)$answer,
            "correct" => ((string)$answer["correct"] === "true")
        ];
    }

//mijesanje odgovora jer je u xml bazi prvi odgovor uvijek tocan    
    shuffle($answers);

//ucitavanje id-a i teksta trenutnog pitanja  
    $_SESSION["current_question"] = [
        "text" => $questions_text,
        "id" => $questions_id
    ];

//ucitavanje trenutacnog odgovora    
    $_SESSION["current_answers"] = $answers;
}

//slanje odgovora i provjera, te povećanje/smanjenje vrijednosti brojaca
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["answer"])) {
    $selectedAnswerText = $_POST["answer"];

    foreach ($_SESSION["current_answers"] as $answer) {
        if ($answer["text"] === $selectedAnswerText) {
            if ($answer["correct"]) {
                $_SESSION["correct"]++;
            } else {
                $_SESSION["incorrect"]++;
            }
            break;
        }
    }

//brisanje trenutacnog odgovora kod slanja ili osvjezavanja stranice
    unset($_SESSION["current_question"], $_SESSION["current_answers"]);

//bez ovoga ne radi?
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}

//podatci za prikaz trenutacnih podataka
if (isset($_SESSION["current_question"])) {
    $questions_text = $_SESSION["current_question"]["text"];
    $questions_id = $_SESSION["current_question"]["id"];
}
$answers = $_SESSION["current_answers"];
?>

<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>XML Kviz</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .wrapper { min-height: 100vh; display: flex; flex-direction: column; }
  </style>
</head>
<body>
  <div class="wrapper bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">XML Projekt - Kviz</a>
      </div>
    </nav>

    <div class="container bg-dark py-4">
      <div class="row align-self-center" style="min-height: 160px;">
        <div class="col text-center">
          <h1 class="text-white"><?=$questions_text?></h1>
          <p class="text-white py-2">ID: <?= $questions_id ?></p>
        </div>
      </div>

      <form method="post" action="">
        <div class="row g-2 mt-4">
          <?php foreach ($answers as $i => $ans): ?>
            <div class="col-12 mb-3 py-2">
              <input
                type="radio"
                class="btn-check"
                name="answer"
                id="opt<?= $i ?>"
                autocomplete="off"
                value="<?=$ans["text"]?>"
              />
              <label
                class="btn btn-outline-light w-100 text-center py-2"
                for="opt<?= $i ?>"
              ><?=$ans["text"]?></label>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="row mt-4 py-4">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-success px-5 py-3">Potvrdi</button>
          </div>
        </div>

        <div class="row mt-4 py-2">
          <div class="col-12 text-center">
            <button type="submit" name="submit" value="reset" class="btn btn-outline-danger px-3 py-1 ms-1">Resetiraj brojač</button>
          </div>
        </div>
      </form>

      <div class="row py-4">
        <div class="col text-center">
          <p class="text-white">
            Točno: <?= $_SESSION["correct"] ?> | Netočno: <?= $_SESSION["incorrect"] ?>
          </p>
        </div>
      </div>
    </div>

    <footer class="bg-dark text-center py-3 mt-auto">
      <div class="container text-white">
        Josip Martić, 2025., Tehničko Veleučilište u Zagrebu<br />
        Ak. god. 2024. / 2025., kolegij XML Programiranje
      </div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
