import sys
import json
import numpy as np
import os

# Define o diretório onde o matplotlib vai guardar as configs temporárias.
os.environ["MPLCONFIGDIR"] = "C:/laragon/www/Tauba/temp_matplotlib_config"

import matplotlib.pyplot as plt

# Recebe o JSON passado pelo Laravel.
dadosJSON = sys.argv[1]
dados = json.loads(dadosJSON)

# Solução ótima.
solucao_otima = float(dados["solucao_otima"])

# Função objetivo.
func_obj = dados["funcao_objetivo"]
c1 = float(func_obj["1"])
c2 = float(func_obj["2"])

# Restrições.
restricoes = dados["restricoes"]

# Inicializar limites para escala.
xmax = 0
ymax = 0

# Definindo o intervalo para x.
x = np.linspace(0, 50, 500)

# Criar uma figura.
plt.figure(figsize=(8, 6))

# Plotar cada restrição.
for restricao in restricoes:
    coef = restricao['coeficientes']
    sinal = restricao['sinal']
    termo = float(restricao['termo'])

    # Trata os casos para problemas de uma ou duas variáveis.
    if len(coef) == 1:
        a = float(coef["1"])
        b = 0

    if len(coef) == 2:
        a = float(coef["1"])
        b = float(coef["2"])

    if a != 0:
        intersec_x = termo / a
        if intersec_x > xmax:
            xmax = intersec_x

    if b != 0:
        intersec_y = termo / b
        if intersec_y > ymax:
            ymax = intersec_y

    # Evitar divisão por zero.
    if b != 0:
        y = (termo - a * x) / b
    else:
        y = np.full_like(x, np.nan)
        y[x == (termo / a)] = 0

    # Plot da reta.
    label = f"{a}x + {b}y {sinal} {termo}"
    plt.plot(x, y, label=label)

    # Preencher a região.
    if sinal == "<=":
        plt.fill_between(x, 0, y, where=(y >= 0), alpha=0.2)
    elif sinal == ">=":
        plt.fill_between(x, y, 50, where=(y >= 0), alpha=0.2)

# Definindo limites do gráfico.
lim_sup = (max(xmax, ymax)) + 1
plt.xlim(0, lim_sup)
plt.ylim(0, lim_sup)

# Gerar grid de pontos.
X, Y = np.meshgrid(x, x)

# Calcular valor da função objetivo em cada ponto.
Z = c1 * X + c2 * Y

# Define valores de isovalor entre 0 e a solução ótima.
valores_obj = np.linspace(0, solucao_otima, 6)

# Plotar as curvas de nível.
contours = plt.contour(X, Y, Z, valores_obj, colors='red', linestyles='dashed')
plt.clabel(contours, inline=True, fontsize=8, fmt="%.0f")

# Configurações.
plt.xlabel('x')
plt.ylabel('y')
plt.title('Região de Viabilidade - Simplex')
plt.grid(True)
plt.legend()

# Salvar o gráfico em arquivo.
caminho_saida = '../public/graficos/solucao.png'
plt.savefig(caminho_saida)

# Nome do arquivo.
file_name = 'solucao.png'

# Retorno pro Laravel.
print(json.dumps({"nome": file_name}))
