# LOGBOOK2 — CVE-2014-0160 (Heartbleed)

## Identificação
- Falha na extensão TLS/DTLS Heartbeat do OpenSSL.
- Permite leitura arbitrária de memória do processo (buffer over-read).
- Afeta OpenSSL 1.0.1 até 1.0.1f; corrigido em 1.0.1g.
- Exposto em servidores HTTPS, VPNs e outros serviços que usam OpenSSL.

## Catalogação
- Identificado e publicado em abril de 2014 (CVE-2014-0160).
- Classificado como vulnerabilidade crítica; análises no NVD.
- Referências públicas e múltiplos advisories nas bases CVE/NVD.
- Documentação histórica e notas técnicas também no Exploit-DB.

## Exploit
- PoCs públicos em Python e C demonstram fuga de memória (Exploit-DB).
- Exploits enviam pedidos Heartbeat com tamanho manipulado.
- Entradas e PoCs indexadas em Exploit-DB (vários IDs disponíveis).
- Automação e scanners surgiram rapidamente após divulgação.

## Ataques
- Exploração permitiu leitura de chaves privadas em memória.
- Vazamento de credenciais e dados sensíveis de serviços afetados.
- Amplamente explorado e coberto pela imprensa e vendors.
- Casos práticos e testes disponíveis em repositórios públicos.

## Correção / contramedidas
- Atualizar OpenSSL para 1.0.1g ou versão posterior (patch oficial).
- Revogar e renovar certificados se houver suspeita de exposição.
- Usar scanners/IDS para detetar instâncias vulneráveis e assinaturas.
- Implementar políticas de gestão de patches e validar pós-correção.


---

## Referências (consultadas — apenas os cinco sites indicados)
- https://cve.mitre.org
- https://www.exploit-db.com
- https://nvd.nist.gov
- https://www.cvedetails.com/
- https://cwe.mitre.org

