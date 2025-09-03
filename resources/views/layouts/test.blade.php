<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mario Leaderboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <style>
    /* Mario-inspired theme variables */
    :root {
      --mario-red: #e74c3c;
      --mario-blue: #3498db;          
      --mario-yellow: #f1c40f;
      --mario-bg: #2c3e50;
      --mario-brick: #c1440e;
      --highlight: #27ae60;
    }

    body {
      font-family: 'Press Start 2P', cursive;
      margin: 0;
      background: var(--mario-bg);
      color: var(--mario-yellow);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem;
    }

    h1 {
      font-size: 1.5rem;
      background-color: var(--mario-red);
      color: white;
      padding: 1rem;
      border: 4px solid var(--mario-brick);
      text-align: center;
      margin-bottom: 2rem;
      box-shadow: 0 0 10px var(--mario-yellow);
    }

    .leaderboard {
      width: 100%;
      max-width: 900px;
      overflow-x: auto;
      border: 4px solid var(--mario-red);
      background-color: var(--mario-blue);
      box-shadow: 0 0 20px var(--mario-yellow);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      color: white;
    }

    thead {
      background-color: var(--mario-brick);
      color: var(--mario-yellow);
    }

    tbody tr {
      transition: background 0.3s ease;
    }

    tbody tr:nth-child(even) {
      background-color: #2980b9;
    }

    tbody tr:hover {
      background-color: #1abc9c;
    }

    /* .rank-1 td:first-child::after {
      content: " 🥇 ";
      color: var(--mario-yellow);
    }

    .rank-2 td:first-child::after {
      content: " 🥈 ";
      color: #ecf0f1;
    }

    .rank-3 td:first-child::after {
      content: " 🥉 ";
      color: #cd7f32;
    } */

    .current-user {
      border-left: 5px solid var(--highlight);
      background-color: #16a085 !important;
    }

    .buttons {
      margin-top: 1rem;
    }

    .buttons button {
      font-family: 'Press Start 2P', cursive;
      background-color: var(--mario-red);
      color: white;
      padding: 1rem;
      border: 4px solid var(--mario-yellow);
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .buttons button:hover {
      transform: scale(1.05);
      background-color: var(--mario-yellow);
      color: black;
    }

    @media screen and (max-width: 600px) {
      th, td {
        font-size: 0.6rem;
        padding: 0.5rem;
      }
    }
.podium {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 3rem;
}

.podium-item {
  text-align: center;
  background-color: var(--mario-blue);
  color: white;
  padding: 1rem;
  border: 4px solid var(--mario-yellow);
  box-shadow: 0 0 10px var(--mario-yellow);
  width: 100px;
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.podium-item:hover {
  transform: translateY(-5px);
}

.podium-item .medal {
  font-size: 1.5rem;
}

.podium-item .username {
  margin-top: 0.5rem;
  font-size: 0.8rem;
  font-weight: bold;
}

.podium-item .score {
  font-size: 0.9rem;
  color: var(--mario-yellow);
}

/* Heights to simulate podium levels */
.first {
  height: 200px;
  background-color: var(--mario-red);
}

.second {
  height: 160px;
  background-color: var(--mario-brick);
}

.third {
  height: 140px;
  background-color: #cd7f32;
}
.podium-item .avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: 3px solid var(--mario-yellow);
  margin-bottom: 0.5rem;
  box-shadow: 0 0 10px var(--mario-yellow);
}
  </style>
</head>
<body>

  <h1>🏁 Leaderboard</h1>
<div class="podium">
  <div class="podium-item second">
    <img src="{{ isset($topKaryawan[1]->foto) ? asset('storage/'.$topKaryawan[1]->foto) : asset('css/default-profile.jpg') }}" alt="playerX" class="avatar" />
    <div class="medal">🥈</div>
    <div class="username">playerX</div>
    {{-- <div class="score">4500</div> --}}
  </div>
  <div class="podium-item first">
    <img src="{{ isset($topKaryawan[1]->foto) ? asset('storage/'.$topKaryawan[1]->foto) : asset('css/default-profile.jpg') }}" alt="user123" class="avatar" />
    <div class="medal">🥇</div>
    <div class="username">user123</div>
    {{-- <div class="score">5000</div> --}}
  </div>
  <div class="podium-item third">
    <img src="{{ isset($topKaryawan[1]->foto) ? asset('storage/'.$topKaryawan[1]->foto) : asset('css/default-profile.jpg') }}" alt="champNinja" class="avatar" />
    <div class="medal">🥉</div>
    <div class="username">champNinja</div>
    {{-- <div class="score">4200</div> --}}
  </div>
</div>  <div class="leaderboard">
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Username</th>
          <th>Score</th>
          <th>Level</th>
        </tr>
      </thead>
      <tbody>
        <tr class="rank-1 current-user">
          <td>4</td>
          <td>user123</td>
          <td>5000</td>
          <td>Master</td>
        </tr>
        <tr class="rank-2">
          <td>5</td>
          <td>playerX</td>
          <td>4500</td>
          <td>Pro</td>
        </tr>
        <tr class="rank-3">
          <td>6</td>
          <td>champNinja</td>
          <td>4200</td>
          <td>Elite</td>
        </tr>
        <tr>
          <td>7</td>
          <td>rookie</td>
          <td>3900</td>
          <td>Advanced</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="buttons">
    <button onclick="alert('Data refreshed!')">🔄 REFRESH</button>
    <button onclick="alert('Loading...')">⬇️ LOAD MORE</button>
  </div>

</body>
</html>