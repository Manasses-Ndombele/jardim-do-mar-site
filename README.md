# Assembleia Cristã Jardim do Mar — Website PHP

Site institucional completo construído em **PHP puro** com HTML5, CSS3 e JavaScript vanilla. Sem frameworks, sem dependências externas — apenas um servidor PHP 7.4+.

---

## Estrutura do Projecto

```
jardim-do-mar/
│
├── index.php                        ← Página inicial
├── sobre.php                        ← Página Sobre Nós
│
├── multimedia/
│   ├── index.php                    ← Galeria de fotos
│   └── pregacoes.php                ← Pregações & Áudios (custom audio player)
│
├── doutrina/
│   ├── index.php                    ← Lista de artigos doutrinários
│   ├── william-branham.php          ← Artigo: William Marrion Branham
│   ├── ewald-frank.php              ← Artigo: Ewald Frank
│   ├── batismo-por-imersao.php      ← Artigo: Batismo Por Imersão
│   ├── unicismo.php                 ← Artigo: Unicismo
│   └── _article_template.php       ← Template reutilizável de artigo
│
├── includes/
│   ├── header.php                   ← Header/navbar partilhado
│   └── footer.php                   ← Footer partilhado
│
└── assets/
    ├── css/
    │   └── global.css               ← Estilos globais completos
    └── js/
        └── global.js                ← JavaScript global (navbar, lightbox, reveal)
```

---

## Requisitos

- **PHP** 7.4 ou superior
- Qualquer servidor web: **Apache**, **Nginx**, ou o servidor embutido do PHP
- **Não requer** base de dados para as páginas públicas

---

## Como Executar Localmente

### Opção 1: Servidor embutido do PHP
```bash
cd jardim-do-mar
php -S localhost:8000
```
Abra o browser em: `http://localhost:8000`

### Opção 2: XAMPP / WAMP / LAMP
1. Copie a pasta `jardim-do-mar/` para `htdocs/` (XAMPP) ou `www/` (WAMP)
2. Acesse: `http://localhost/jardim-do-mar/`

### Opção 3: Servidor de produção (Apache)
1. Faça upload de todos os ficheiros para o directório raiz do servidor (ex: `/var/www/html/`)
2. Certifique-se que `mod_rewrite` está activado se usar `.htaccess`

---

## Personalização

### Substituir imagens placeholder
Todas as imagens usam `https://placehold.co/` como placeholder.  
Substitua os atributos `src` pelas URLs reais ou caminhos locais:

```php
// Exemplo — substituir em multimedia/index.php:
['src' => 'assets/images/culto-01.jpg', 'alt' => 'Culto Dominical'],
```

### Adicionar pregações reais
No ficheiro `multimedia/pregacoes.php`, edite o array `$pregacoes`:

```php
$pregacoes = [
    ['id' => 1, 'titulo' => 'Gozo Eterno', 'pregador_id' => 1,
     'data' => '2024-04-11', 'audio' => 'https://servidor.com/audio.mp3'],
    // ...
];
```

### Integrar base de dados (opcional)
Para integrar com MySQL/MariaDB, crie um ficheiro `includes/db.php`:

```php
<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=jardim_do_mar;charset=utf8mb4',
    'usuario',
    'senha',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
```

E substitua os arrays estáticos por queries:
```php
$stmt = $pdo->query('SELECT * FROM pregacoes ORDER BY data_pregacao DESC');
$pregacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Configurar link YouTube (Live)
Em `index.php` e `sobre.php`, substitua o `channel=UCxxxxxxxxxxxxxxx` pelo ID real do canal.

### Configurar contactos
Em `index.php`, edite o número e email:
- Telefone: `+244 923 224 456`
- Email: `jardimdomar@gmail.com`

---

## Páginas Incluídas

| Página                           | URL                                | Descrição                           |
|----------------------------------|------------------------------------|-------------------------------------|
| Início                           | `/index.php`                       | Hero, intro, galeria, programa, mapa|
| Multimídia — Galeria             | `/multimedia/index.php`            | Galeria completa com lightbox        |
| Multimídia — Pregações & Áudios  | `/multimedia/pregacoes.php`        | Custom audio player + filtros        |
| Doutrina                         | `/doutrina/index.php`              | Cards dos artigos doutrinários       |
| Doutrina — William Branham       | `/doutrina/william-branham.php`    | Artigo completo                      |
| Doutrina — Ewald Frank           | `/doutrina/ewald-frank.php`        | Artigo completo                      |
| Doutrina — Batismo por Imersão   | `/doutrina/batismo-por-imersao.php`| Artigo completo                      |
| Doutrina — Unicismo              | `/doutrina/unicismo.php`           | Artigo completo                      |
| Sobre                            | `/sobre.php`                       | História, valores, galeria construção|

---

## Funcionalidades Técnicas

- ✅ Navbar fixa com glassmorphism ao scroll
- ✅ Menu hamburger responsivo para mobile
- ✅ Lightbox para galeria de fotos
- ✅ Custom Audio Player (play/pause, progresso, volume, download)
- ✅ Filtro de pregações por título, data e pregador (GET params)
- ✅ Scroll reveal animations (IntersectionObserver)
- ✅ Fontes: Cinzel (títulos) + Lato (corpo) via Google Fonts
- ✅ Totalmente responsivo: desktop, tablet, mobile
- ✅ Acessibilidade: atributos `aria-*`, `role`, `alt`, `tabindex`
- ✅ SEO: meta description, Open Graph tags
- ✅ Paleta institucional: Azul escuro + Vermelho

---

## Paleta de Cores

| Nome            | HEX       |
|-----------------|-----------|
| Azul Escuro     | `#0D2B5E` |
| Azul Médio      | `#1A5276` |
| Azul Claro      | `#2E86C1` |
| Vermelho        | `#C0392B` |
| Cinza Footer    | `#111927` |
| Cinza BG        | `#F4F7FB` |

---

## Créditos

- **Fontes**: [Google Fonts — Cinzel & Lato](https://fonts.google.com)
- **Ícones SVG**: Inline, sem dependência externa
- **Imagens**: [placehold.co](https://placehold.co) (substituir por imagens reais)
- **Avatares**: [ui-avatars.com](https://ui-avatars.com) (substituir por fotos reais)

---

*Assembleia Cristã Jardim do Mar — Município do Cazenga, Bairro São João (Cuca), Rua Jardim do Mar, Luanda, Angola*
