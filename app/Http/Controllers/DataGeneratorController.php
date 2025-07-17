<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataGeneratorController extends Controller
{
    public function index()
    {
        return view('generator.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,cpf,password,phone,name,cnpj,rg',
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $type = $request->input('type');
        $quantity = $request->input('quantity');
        $data = [];

        for ($i = 0; $i < $quantity; $i++) {
            $data[] = $this->generateData($type);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'type' => $type,
            'quantity' => $quantity
        ]);
    }

    private function generateData($type)
    {
        return match($type) {
            'email' => $this->generateEmail(),
            'cpf' => $this->generateCPF(),
            'cnpj' => $this->generateCNPJ(),
            'rg' => $this->generateRG(),
            'password' => $this->generatePassword(),
            'phone' => $this->generatePhone(),
            'name' => $this->generateName(),
            default => 'Tipo não suportado'
        };
    }

    private function generateEmail()
    {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'uol.com.br'];
        $prefixes = ['user', 'teste', 'admin', 'contato', 'info'];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $number = rand(100, 9999);
        $domain = $domains[array_rand($domains)];
        
        return $prefix . $number . '@' . $domain;
    }

    private function generateCPF()
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= rand(0, 9);
        }
        
        // Calcular dígitos verificadores
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $cpf[$i] * (10 - $i);
        }
        $resto = $soma % 11;
        $dv1 = $resto < 2 ? 0 : 11 - $resto;
        
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $cpf[$i] * (11 - $i);
        }
        $soma += $dv1 * 2;
        $resto = $soma % 11;
        $dv2 = $resto < 2 ? 0 : 11 - $resto;
        
        $cpf .= $dv1 . $dv2;
        
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    private function generateCNPJ()
    {
        $cnpj = '';
        for ($i = 0; $i < 8; $i++) {
            $cnpj .= rand(0, 9);
        }
        $cnpj .= '0001';

        // Calcular dígitos verificadores
        $soma = 0;
        $pesos = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $pesos[$i];
        }
        $resto = $soma % 11;
        $dv1 = $resto < 2 ? 0 : 11 - $resto;

        $soma = 0;
        $pesos = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $pesos[$i];
        }
        $soma += $dv1 * 2;
        $resto = $soma % 11;
        $dv2 = $resto < 2 ? 0 : 11 - $resto;

        $cnpj .= $dv1 . $dv2;

        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }

    private function generateRG()
    {
        $rg = '';
        for ($i = 0; $i < 8; $i++) {
            $rg .= rand(0, 9);
        }
        
        $dv = chr(rand(65, 90));
        
        return substr($rg, 0, 2) . '.' . substr($rg, 2, 3) . '.' . substr($rg, 5, 3) . '-' . $dv;
    }

    private function generatePassword($length = 12)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];
        
        $allChars = $lowercase . $uppercase . $numbers . $special;
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    private function generatePhone()
    {
        $ddds = [11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28, 31, 32, 33, 34, 35, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69, 71, 73, 74, 75, 77, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96, 97, 98, 99];
        $ddd = $ddds[array_rand($ddds)];
        $number = '9' . rand(1000, 9999) . rand(1000, 9999);
        
        return "({$ddd}) {$number}";
    }

    private function generateName()
    {
        $nomes = [
            'João', 'Maria', 'Pedro', 'Ana', 'Carlos', 'Fernanda', 'Roberto', 'Juliana', 
            'Marcos', 'Larissa', 'Rafael', 'Camila', 'Bruno', 'Beatriz', 'Lucas', 'Mariana',
            'Diego', 'Patrícia', 'Rodrigo', 'Débora', 'Felipe', 'Carla', 'Gustavo', 'Renata'
        ];
        
        $sobrenomes = [
            'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira',
            'Lima', 'Gomes', 'Ribeiro', 'Carvalho', 'Castro', 'Almeida', 'Soares', 'Nascimento'
        ];
        
        $nome = $nomes[array_rand($nomes)];
        $sobrenome = $sobrenomes[array_rand($sobrenomes)];
        
        return $nome . ' ' . $sobrenome;
    }
}