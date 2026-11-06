#!/usr/bin/env bash
# Testes de ponta a ponta do login e das páginas protegidas.
# Uso: BASE=http://localhost:8000 bash tests/seguranca.sh
set -u
BASE="${BASE:-http://localhost:8000}"
ADMIN="$BASE/administra%C3%A7%C3%A3o.php"
falhas=0
tmp=$(mktemp -d)

# Espera o Apache responder (até 60 s)
for _ in $(seq 1 30); do
  curl -s -o /dev/null "$BASE/login.php" && break
  sleep 2
done

ok()   { echo "ok   - $1"; }
erro() { echo "FALHA - $1"; falhas=$((falhas + 1)); }

# status, Location e corpo de uma requisição; $1 = jar de cookies, demais = args do curl
req() {
  local jar="$1"; shift
  curl -s -o "$tmp/corpo" -D "$tmp/cab" -b "$jar" -c "$jar" "$@"
  status=$(head -1 "$tmp/cab" | awk '{print $2}')
  local_=$(grep -i '^location:' "$tmp/cab" | tr -d '\r' | awk '{print $2}')
  tamanho=$(wc -c < "$tmp/corpo")
}

# 1. Página protegida sem login: redireciona e não envia o HTML
req "$tmp/anon" "$BASE/index.php"
[ "$status" = 302 ] && [ "$local_" = login.php ] && [ "$tamanho" -lt 200 ] \
  && ok "index.php sem login redireciona sem enviar a página" \
  || erro "index.php sem login: status=$status location=$local_ bytes=$tamanho"

# 2. Login de admin com senha errada não abre o painel
req "$tmp/ataque" "$BASE/login.php" --data-urlencode "email_log=admin@gmail.com" \
  --data-urlencode "password_log=senha-errada" -d "login=Login"
req "$tmp/ataque" "$ADMIN"
[ "$status" = 302 ] && [ "$tamanho" -lt 200 ] \
  && ok "senha errada não dá acesso ao painel de administração" \
  || erro "painel com senha errada: status=$status bytes=$tamanho"

# 3. SQL injection no login não autentica
req "$tmp/sqli" "$BASE/login.php" --data-urlencode "email_log=' OR '1'='1' -- " \
  --data-urlencode "password_log=x" -d "login=Login"
[ "$status" = 200 ] && grep -q "E-mail ou senha inválidos" "$tmp/corpo" \
  && ok "SQL injection no login é recusada" \
  || erro "SQL injection: status=$status location=$local_"

# 4. Cadastro com nome malicioso e login válido
email="teste$RANDOM@exemplo.com"
req "$tmp/user" "$BASE/login.php" --data-urlencode "fullname_reg=<script>alert(1)</script>" \
  --data-urlencode "email_reg=$email" --data-urlencode "tell_reg=21999999999" \
  --data-urlencode "password_reg=senha-forte-123" -d "register=Registrar"
grep -q "Registrado com sucesso" "$tmp/corpo" && ok "cadastro" || erro "cadastro falhou"

req "$tmp/user" "$BASE/login.php" --data-urlencode "email_reg=$email" \
  --data-urlencode "fullname_reg=x" --data-urlencode "tell_reg=1" \
  --data-urlencode "password_reg=senha-forte-123" -d "register=Registrar"
grep -q "já está registrado" "$tmp/corpo" && ok "e-mail duplicado é recusado" || erro "e-mail duplicado aceito"

req "$tmp/user" "$BASE/login.php" --data-urlencode "email_log=$email" \
  --data-urlencode "password_log=senha-forte-123" -d "login=Login"
[ "$status" = 302 ] && [ "$local_" = index.php ] \
  && ok "login válido redireciona para index.php" \
  || erro "login válido: status=$status location=$local_"

# 5. Nome do usuário sai escapado (sem XSS armazenado)
req "$tmp/user" "$BASE/index.php"
if [ "$status" = 200 ] && grep -q "&lt;script&gt;alert(1)&lt;/script&gt;" "$tmp/corpo" \
   && ! grep -q "<script>alert(1)</script>" "$tmp/corpo"; then
  ok "nome do usuário é escapado no menu"
else
  erro "nome sem escape ou index.php inacessível (status=$status)"
fi

# 6. Usuário comum não acessa o painel
req "$tmp/user" "$ADMIN"
[ "$status" = 302 ] && [ "$tamanho" -lt 200 ] \
  && ok "usuário comum é barrado no painel" \
  || erro "usuário comum no painel: status=$status bytes=$tamanho"

rm -rf "$tmp"
echo
[ "$falhas" -eq 0 ] && echo "Todos os testes passaram." || { echo "$falhas teste(s) falharam."; exit 1; }
