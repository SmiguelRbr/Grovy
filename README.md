# Grovy - Platform de Saúde e Bem-estar

Uma plataforma moderna de saúde que conecta pacientes com profissionais especializados (nutricionistas e personal trainers), permitindo acompanhamento personalizado através de planos de dieta, treino e evolução de medidas.

## 📋 Índice

- [Requisitos do Sistema](#requisitos-do-sistema)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Migrations](#migrations)
- [Estrutura da API](#estrutura-da-api)
- [Autenticação](#autenticação)
- [Rotas Públicas](#rotas-públicas)
- [Rotas Protegidas](#rotas-protegidas)
- [Modelos de Dados](#modelos-de-dados)
- [Desenvolvimento](#desenvolvimento)

## 🔧 Requisitos do Sistema

### Software

- **PHP**: 8.2 ou superior
- **Composer**: 2.0+
- **Node.js**: 18.0+
- **npm**: 9.0+
- **MySQL**: 8.0+ ou **SQLite** (para desenvolvimento)
- **Git**: 2.0+

### Extensões PHP Necessárias

- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath

### Requisitos para Produção

- Servidor web (Apache com mod_rewrite ou Nginx)
- SSL/TLS certificado
- Suporte a arquivos estáticos (para upload de imagens)

## 📦 Instalação

### 1. Clone o Repositório

```bash
git clone <seu-repositorio>
cd grovy
```

### 2. Instale as Dependências PHP

```bash
composer install
```

### 3. Instale as Dependências JavaScript

```bash
npm install
```

### 4. Configure as Variáveis de Ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o arquivo `.env` com suas configurações:

```env
APP_NAME=Grovy
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grovy
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
```

### 5. Execute as Migrations

```bash
php artisan migrate
```

### 6. Build dos Assets

```bash
npm run build
```

Ou em desenvolvimento com hot reload:

```bash
npm run dev
```

## 🗄️ Migrations

### Rodando Migrations

Execute todas as migrations pendentes:

```bash
php artisan migrate
```

**Opções úteis:**

```bash
# Rodar migrations com step (controla quantas migrations executar)
php artisan migrate --step

# Fazer rollback da última migration
php artisan migrate:rollback

# Fazer rollback de todas as migrations
php artisan migrate:reset

# Rollback + migrate (útil em desenvolvimento)
php artisan migrate:refresh

# Rollback + migrate + seed
php artisan migrate:refresh --seed

# Forçar migration em produção (perigoso!)
php artisan migrate --force
```

### Tabelas Criadas

1. **users** - Usuários do sistema
2. **roles** - Papéis/funções (admin, nutricionista, personal, paciente)
3. **patient_details** - Detalhes dos pacientes (altura, peso, objetivo, etc)
4. **professional_profiles** - Perfis profissionais (CRN/CREF, bio, aprovação)
5. **measurements** - Registro de medidas e peso do paciente
6. **contracts** - Vínculos entre pacientes e profissionais
7. **plans** - Planos de dieta e treino
8. **contents** - Conteúdos/dicas publicadas pelos profissionais
9. **password_reset_tokens** - Tokens de reset de senha
10. **sessions** - Sessões do usuário
11. **cache** - Cache de aplicação
12. **jobs** - Fila de jobs
13. **job_batches** - Batches de jobs

## 🏗️ Estrutura da API

### URL Base

```
http://localhost:8000/api
```

### Headers Obrigatórios (Requisições Autenticadas)

```http
Authorization: Bearer {TOKEN}
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

## 🔐 Autenticação

### Conceitos

A API utiliza **Laravel Sanctum** para autenticação baseada em tokens Bearer. Após login bem-sucedido, o cliente recebe um token que deve ser incluído em todas as requisições subsequentes.

### Fluxo de Autenticação

1. **Registro**: POST `/api/register`
2. **Login**: POST `/api/login`
3. **Usar Token**: Incluir `Authorization: Bearer {token}` em requisições protegidas
4. **Logout**: POST `/api/logout` (destrói o token)

---

## 🌐 Rotas Públicas

Acessíveis **sem autenticação** (guest).

### Registro de Usuário

```http
POST /api/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "profile_image": "file" (opcional)
}
```

**Resposta (201)**:
```json
{
  "message": "Usuario criado com sucesso"
}
```

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Resposta (200)**:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "message": "Usuario cadastrado com sucesso"
}
```

---

## 🔒 Rotas Protegidas

Requerem autenticação via token Bearer.

### Logout

```http
POST /api/logout
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
{
  "message": "Logout realizado com sucesso."
}
```

---

## 👤 Rotas Comuns (Todos Usuários Autenticados)

### Onboarding - Completar Perfil de Paciente

```http
POST /api/perfil/paciente
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "nascimento": "1995-05-15",
  "genero": "M",
  "altura": 1.80,
  "peso": 85.5,
  "objetivo": "Ganhar massa muscular"
}
```

**Resposta (200)**:
```json
{
  "message": "Perfil de paciente salvo!",
  "data": { ... }
}
```

### Onboarding - Completar Perfil Profissional

```http
POST /api/perfil/profissional
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "crn_cref": "CRN123456",
  "bio": "Nutricionista com 5 anos de experiência"
}
```

**Resposta (200)**:
```json
{
  "message": "Perfil profissional salvo! Aguarde aprovação.",
  "data": { ... }
}
```

### Listar Profissionais Aprovados

```http
GET /api/profissionais
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
[
  {
    "id": 1,
    "name": "Dra. Maria",
    "email": "maria@example.com",
    "role": {
      "name": "nutricionista"
    },
    "professional_profile": {
      "bio": "Especialista em nutrição esportiva",
      "aprovado": true
    }
  }
]
```

### Visualizar Profissional Específico

```http
GET /api/profissionais/{id}
Authorization: Bearer {TOKEN}
```

### Listar Conteúdos de um Profissional

```http
GET /api/profissionais/{id}/contents
Authorization: Bearer {TOKEN}
```

---

## 📊 Rotas de Paciente

Acessíveis apenas por usuários com role **`paciente`**.

### Dashboard - Evolução de Peso

```http
GET /api/dashboard/evolution
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
{
  "overview": {
    "peso_atual": 85.5,
    "inicio": 90.0,
    "diferenca": -4.5,
    "registros_total": 12
  },
  "chart": {
    "labels": ["01/02", "08/02", "15/02"],
    "data": [90.0, 87.5, 85.5]
  }
}
```

### Recomendações de Profissionais

```http
GET /api/dashboard/recommendations
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
[
  {
    "id": 2,
    "name": "Pedro Trainer",
    "professional_profile": {
      "bio": "Personal trainer certificado"
    }
  }
]
```

### Registrar Medidas

```http
POST /api/measurements
Authorization: Bearer {TOKEN}
Content-Type: multipart/form-data

{
  "peso": 85.5,
  "recorded_at": "2026-02-15",
  "waist_cm": 85.0 (opcional),
  "hips_cm": 95.0 (opcional),
  "chest_cm": 100.0 (opcional),
  "notes": "Sinto-me mais disposto" (opcional),
  "photo_front": file (opcional, max 5MB),
  "photo_side": file (opcional, max 5MB),
  "photo_back": file (opcional, max 5MB)
}
```

**Resposta (201)**:
```json
{
  "message": "Registrado com sucesso!",
  "data": { ... }
}
```

### Listar Medidas do Paciente

```http
GET /api/measurements
Authorization: Bearer {TOKEN}
```

### Solicitar Acompanhamento a um Profissional

```http
POST /api/contracts
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "professional_id": 2
}
```

**Resposta (201)**:
```json
{
  "message": "Solicitação enviada com sucesso! Aguarde o aceite."
}
```

### Visualizar Plano Ativo

```http
GET /api/my-plan
Authorization: Bearer {TOKEN}
```

---

## 👨‍⚕️ Rotas de Profissional

Acessíveis apenas por usuários com role **`nutricionista`** ou **`personal`**.

### Listar Solicitações Pendentes

```http
GET /api/contracts/requests
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
[
  {
    "id": 1,
    "student": {
      "id": 5,
      "name": "João",
      "patient_details": {
        "peso": 85.5,
        "objetivo": "Ganhar massa"
      }
    },
    "status": "pending"
  }
]
```

### Aceitar Solicitação de Paciente

```http
PATCH /api/contracts/{id}/accept
Authorization: Bearer {TOKEN}
```

**Resposta (200)**:
```json
{
  "message": "Aluno aceito! Agora você pode criar planos para ele."
}
```

### Rejeitar Solicitação

```http
PATCH /api/contracts/{id}/reject
Authorization: Bearer {TOKEN}
```

### Listar Pacientes Vinculados

```http
GET /api/pacientes
Authorization: Bearer {TOKEN}
```

### Visualizar Paciente Específico

```http
GET /api/pacientes/{id}
Authorization: Bearer {TOKEN}
```

### Listar Planos de um Paciente

```http
GET /api/pacientes/{id}/plans
Authorization: Bearer {TOKEN}
```

### Criar Plano (Dieta/Treino)

```http
POST /api/plans
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "student_id": 5,
  "title": "Plano de Ganho Muscular",
  "type": "workout",
  "description": "Focado em hipertrofia",
  "content": {
    "semana_1": [
      {
        "dia": "Segunda",
        "exercicios": ["Supino", "Rosca Direta"]
      }
    ]
  },
  "expires_at": "2026-03-15" (opcional)
}
```

**Resposta (201)**:
```json
{
  "message": "Plano criado com sucesso!",
  "data": { ... }
}
```

### Publicar Conteúdo (Dica/Artigo)

```http
POST /api/contents
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
  "title": "5 Mitos sobre Nutrição",
  "category": "Nutrição",
  "body": "Conteúdo do artigo aqui..."
}
```

**Resposta (201)**:
```json
{
  "message": "Conteúdo publicado no seu perfil!",
  "data": { ... }
}
```

---

## 📊 Modelos de Dados

### User

```php
class User {
  - id: integer
  - name: string
  - email: string (unique)
  - password: string (hashed)
  - profile_image: string (nullable)
  - email_verified_at: timestamp (nullable)
  - timestamps
  
  relationships:
  - role(): BelongsTo Role
  - patient_details(): HasOne PatientDetail
  - professional_profile(): HasOne ProfessionalProfile
  - measurements(): HasMany Measurement
  - contracts_as_student(): HasMany Contract
  - contracts_as_professional(): HasMany Contract
  - plans(): HasMany Plan
  - contents(): HasMany Content
}
```

### Role

```php
class Role {
  - id: integer
  - user_id: foreign key
  - name: enum('admin', 'nutricionista', 'personal', 'paciente')
  - timestamps
}
```

### PatientDetail

```php
class PatientDetail {
  - id: integer
  - user_id: foreign key
  - nascimento: date
  - genero: string
  - altura: decimal(1,2)
  - peso: decimal(5,2)
  - objetivo: text
  - timestamps
}
```

### ProfessionalProfile

```php
class ProfessionalProfile {
  - id: integer
  - user_id: foreign key
  - CRN/CREF: string
  - bio: text
  - aprovado: boolean (default: false)
  - timestamps
}
```

### Measurement

```php
class Measurement {
  - id: integer
  - user_id: foreign key
  - peso: decimal(5,2)
  - recorded_at: date
  - waist_cm: decimal(5,2) (nullable)
  - hips_cm: decimal(5,2) (nullable)
  - chest_cm: decimal(5,2) (nullable)
  - photo_front_path: string (nullable)
  - photo_side_path: string (nullable)
  - photo_back_path: string (nullable)
  - notes: text (nullable)
  - images: string
  - timestamps
}
```

### Contract

```php
class Contract {
  - id: integer
  - student_id: foreign key
  - professional_id: foreign key
  - status: enum('pending', 'active', 'rejected')
  - timestamps
}
```

### Plan

```php
class Plan {
  - id: integer
  - professional_id: foreign key
  - student_id: foreign key
  - title: string
  - type: enum('diet', 'workout')
  - content: json
  - description: text (nullable)
  - active: boolean (default: true)
  - expires_at: date (nullable)
  - timestamps
}
```

### Content

```php
class Content {
  - id: integer
  - user_id: foreign key
  - title: string
  - category: string
  - body: text
  - image_path: string (nullable)
  - timestamps
}
```

---

## 🚀 Desenvolvimento

### Iniciar Servidor de Desenvolvimento

```bash
# Terminal 1 - Servidor Laravel
php artisan serve

# Terminal 2 - Vite com hot reload
npm run dev
```

Acesse em: `http://localhost:8000`

### Servidor Completo (Recomendado)

```bash
npm run dev
```

Este comando executa simultaneamente:
- Servidor PHP artisan
- Fila de jobs
- Logs em tempo real
- Vite com hot reload

### Outros Comandos Úteis

```bash
# Limpar cache
php artisan cache:clear

# Limpar config
php artisan config:clear

# Limpar views
php artisan view:clear

# Regenerar autoload
composer dump-autoload

# Executar migrations com seed
php artisan migrate:fresh --seed

# Tinker (REPL interativo)
php artisan tinker
```

---

## 📁 Estrutura do Projeto

```
grovy/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── UserController.php
│   │   │   ├── PatientDetailController.php
│   │   │   ├── ProfessionalProfileController.php
│   │   │   ├── MeasurementController.php
│   │   │   ├── ContractController.php
│   │   │   ├── PlanController.php
│   │   │   ├── ContentController.php
│   │   │   └── PatientDashboardController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── PatientDetail.php
│       ├── ProfessionalProfile.php
│       ├── Measurement.php
│       ├── Contract.php
│       ├── Plan.php
│       └── Content.php
├── routes/
│   ├── api.php (rotas da API)
│   ├── web.php
│   └── console.php
├── database/
│   ├── migrations/ (DDL das tabelas)
│   ├── factories/ (factories para testes)
│   └── seeders/ (data seeds)
├── resources/
│   ├── views/ (templates Blade)
│   ├── css/
│   └── js/
├── config/ (configurações da aplicação)
└── storage/ (uploads de usuários)
```

---

## 🔑 Variáveis de Ambiente Importantes

```env
# Aplicação
APP_NAME=Grovy
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grovy
DB_USERNAME=root
DB_PASSWORD=

# Sanctum (Autenticação)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000

# Email (opcional)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com

# Armazenamento
FILESYSTEM_DISK=local
```

---

## 🧪 Testando a API

### Com cURL

```bash
# Registro
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"João","email":"joao@test.com","password":"senha123","password_confirmation":"senha123"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"joao@test.com","password":"senha123"}'

# Requisição Autenticada
curl -X GET http://localhost:8000/api/profissionais \
  -H "Authorization: Bearer {TOKEN}"
```

### Com Postman

1. Importe a coleção de rotas da API
2. Configure a variável `token` após login
3. Use `{{token}}` nos headers das requisições protegidas

### Com Insomnia

Mesmo processo do Postman.

---

## 📝 Notas Importantes

- **Validação**: Todas as rotas implementam validação rigorosa de entrada
- **Segurança**: Middleware `CheckRole` garante acesso apenas a usuários com papéis apropriados
- **Uploads**: Fotos de medidas são armazenadas em `storage/app/public/measurements`
- **Timestamps**: Todas as tabelas incluem `created_at` e `updated_at` automaticamente
- **Soft Deletes**: Não implementados (deletions são permanentes)

---

## 🛟 Suporte e Contribuição

Para reportar bugs ou sugerir melhorias, abra uma issue no repositório.

---

## 📄 Licença

MIT License - veja LICENSE.md para detalhes

---

**Última atualização**: Fevereiro 2026