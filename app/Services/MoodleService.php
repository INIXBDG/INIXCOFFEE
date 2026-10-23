<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoodleService
{
    protected string $url;
    protected string $token;

    public function __construct()
    {
        $this->url   = config('services.moodle.url');
        $this->token = config('services.moodle.token');
    }

    protected function buildQuery(array $params, string $prefix = ''): array
    {
        $query = [];

        foreach ($params as $key => $value) {
            $newKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $query = array_merge($query, $this->buildQuery($value, $newKey));
            } else {
                $query[] = $newKey . '=' . urlencode($value);
            }
        }

        return $query;
    }

    protected function request(string $function, array $params = [])
    {
        try {
            $base = [
                'wstoken'            => $this->token,
                'wsfunction'         => $function,
                'moodlewsrestformat' => 'json',
            ];

            $allParams = array_merge($base, $params);

            $queryString = implode('&', $this->buildQuery($allParams));

            $url = $this->url . '?' . $queryString;

            Log::info('Moodle Full URL', ['url' => $url]);

            $response = Http::timeout(60)->post($url);

            $body = $response->body();

            Log::debug('Moodle Raw Response', [
                'status' => $response->status(),
                'body'   => $body
            ]);

            $json = json_decode($body, true);

            if (trim($body) === 'null') {
                if ($function === 'enrol_manual_enrol_users') {
                    return true;
                }

                Log::warning('Unexpected null response from Moodle', [
                    'function' => $function
                ]);

                return null;
            }

            if (trim($body) === '[]') {
                return $function === 'core_user_get_users_by_field' ? [] : null;
            }

            if (!is_array($json) && $json !== true) {
                    Log::error('Moodle non JSON response', ['body' => $body]);
                return null;
            }

            if (isset($json['exception']) || isset($json['errorcode'])) {
                Log::error('Moodle API Error', [
                    'function' => $function,
                    'response' => $json
                ]);
                return null;
            }

            return $json;

        } catch (\Throwable $e) {
            Log::error('Moodle Connection Error', [
                'message' => $e->getMessage(),
                'function' => $function,
            ]);
            return null;
        }
    }


    public function findUserByEmail(string $email): ?int
    {
        $res = $this->request('core_user_get_users_by_field', [
            'field'  => 'email',
            'values' => [$email],
        ]);

        if (empty($res)) {
            return null;
        }

        $latestUser = collect($res)->sortByDesc('id')->first();

        return $latestUser['id'] ?? null;
    }

    public function createUser($peserta): ?int
    {
        $originalName  = trim($peserta->nama ?? '');
        $originalEmail = trim($peserta->email ?? '');

        if (empty($originalName)) {
            Log::error('Nama kosong', ['peserta' => $peserta]);
            return null;
        }

        $words = preg_split('/\s+/', $this->cleanName($originalName));

        $firstName = $words[0] ?? 'User';

        $lastName = $words[1] ?? 'Peserta';

        $username = $this->generateUsernameFromName($originalName);

        $password = $this->generatePassword();

        $params = [
            'users[0][username]'  => strtolower($originalEmail),
            'users[0][password]'  => $password,
            'users[0][firstname]' => $firstName,
            'users[0][lastname]'  => $lastName,
            'users[0][email]'     => $originalEmail,
        ];

        Log::info('Moodle Create User Params', $params);

        $res = $this->request('core_user_create_users', $params);

        if (is_array($res) && isset($res[0]['id'])) {
            Log::info('User berhasil dibuat di Moodle', [
                'id' => $res[0]['id'],
                'username' => strtolower($originalEmail)
            ]);

            return $res[0]['id'];
        }

        Log::error('Gagal create user Moodle', ['response' => $res]);
        return null;
    }
    
    private function cleanName($name) 
    { 
        return trim(preg_replace('/[^A-Za-z\s\-]/', '', $name)); 
    }

    private function generateUsernameFromName($name)
    {
        $name = $this->cleanName($name);

        $username = str_replace(' ', '', $name);

        return strtolower($username);
    }

    public function syncUser($peserta): ?int
    {
        $userId = $this->findUserByEmail($peserta->email);
        if ($userId) {
            return $userId;
        }
        return $this->createUser($peserta);
    }

    public function enroll(int $userId, int $courseId, int $roleId = 5): bool
    {
        $res = $this->request('enrol_manual_enrol_users', [
            'enrolments' => [[
                'roleid'   => $roleId,
                'userid'   => $userId,
                'courseid' => $courseId,
            ]]
        ]);

        return $res !== null;
    }

    public function findCourse(string $materi, ?string $perusahaan = null): ?int
    {
        // 1. search biasa pakai materi aja
        $courses = $this->searchCourses($materi);

        if (empty($courses)) {
            return null;
        }

        // 2 & 3. kalau ada perusahaan, coba cari yang paling spesifik
        if ($perusahaan) {
            $specificCourse = $this->findSpecificCourse($courses, $perusahaan);

            if ($specificCourse !== null) {
                return $specificCourse;
            }
        }

        // 4. fallback: gak ada yang cocok sama perusahaan, pakai materi doang
        return $this->pickLatestCourse($courses);
    }


    protected function findSpecificCourse(array $courses, string $perusahaan): ?int
    {
        $target = $this->normalize($perusahaan);

        $matched = array_values(array_filter($courses, function ($course) use ($target) {
            $name = $this->normalize($course['fullname'] ?? '');

            return str_contains($name, $target);
        }));

        if (empty($matched)) {
            return null;
        }

        return $this->pickLatestCourse($matched);
    }

    protected function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[\-\|\[\]\(\)_]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    protected function pickLatestCourse(array $courses): ?int
    {
        usort($courses, fn($a, $b) => $b['id'] <=> $a['id']);

        return $courses[0]['id'] ?? null;
    }

    protected function searchCourses(string $query): array
    {
        $res = $this->request('core_course_search_courses', [
            'criterianame'  => 'search',
            'criteriavalue' => $query,
        ]);

        if (!is_array($res) || empty($res['courses'])) {
            return [];
        }

        return $res['courses'];
    }

    protected function generatePassword(): string
    {
        return 'Pa$$w0rd';
    }
}
