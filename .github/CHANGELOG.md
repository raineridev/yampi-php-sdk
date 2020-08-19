# Changelog

Todas as mudanças importantes em `yampi-php-sdk` serão documentadas neste arquivo.

Atualizações devem seguir os princípios de [Keep a CHANGELOG](http://keepachangelog.com/).

## 2020-08-19

Refatoração geral, mudanças profundas no pacote com incompatibilidade com o anterior.

- Todos os namespaces foram renomeados de Dooki\ para Yampi\Api\ .
- Classe Dooki\Dooki removida, agora é necessário usar diretamente Yampi\Api\AuthRequest ou Yampi\Api\Request.
- Classe AuthRequest extende de Request, e não inverso.
- Vários métodos tiveram suas chamadas alteradas, é recomendado validar um por um.
- Adicionado método mágico para facilitar a manipulação da rota.
- Adicionando testes unitários para validar se tudo está correto.

## 2018-02-06

- Retorna os erros quando uma entidade não é processada.
- Agrupa as Exceptions no namespace `Dooki\Exceptions`.
- Tratamento de um response vazio.
